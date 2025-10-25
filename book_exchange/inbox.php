<?php
session_start();
include 'db.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user name
$stmt = $conn->prepare("SELECT name FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Inbox - Resource Exchange</title>
<link rel="stylesheet" href="dashboard.css">
<style>
body {
    font-family: Arial, sans-serif;
    color: #000;
    margin: 0;
    padding: 0;
    background: #f4f4f4;
}
header h2 { color: white; }
header { padding:10px 20px; display:flex; justify-content:space-between; align-items:center; }
header a { color:white; text-decoration:none; margin-left:15px; }
.container { max-width:900px; margin:20px auto; background:#fff; border-radius:10px; box-shadow:0 5px 10px rgba(0,0,0,0.1); display:flex; }
.users-list { width:25%; border-right:1px solid #ddd; overflow-y:auto; height:500px; }
.chat-box { width:75%; padding:20px; display:flex; flex-direction:column; height:500px; }
.user-item { padding:10px; border-bottom:1px solid #eee; cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
.user-item:hover { background:#f0f0f0; }
.message-area { flex:1; overflow-y:auto; padding-bottom:10px; }
.message { padding:10px; margin:5px 0; border-radius:10px; max-width:70%; }
.message.sent { background:#dcf8c6; align-self:flex-end; }
.message.received { background:#fff; border:1px solid #ddd; align-self:flex-start; }
.message-time { font-size:11px; color:#888; margin-top:2px; }
.message-input { display:flex; margin-top:10px; }
.message-input input { flex:1; padding:10px; border-radius:20px; border:1px solid #ccc; outline:none; }
.message-input button { padding:10px 15px; margin-left:5px; border:none; background:#4CAF50; color:#fff; border-radius:20px; cursor:pointer; }
.badge { background:red; color:white; padding:2px 6px; border-radius:50%; font-size:12px; vertical-align:super; }
.delete-btn { background:none;border:none;color:red;cursor:pointer;font-size:12px;float:right; }
</style>
</head>
<body>

<header>
    <h2>📨 Inbox - <?= htmlspecialchars($user_name); ?></h2>
    <div>
        <a href="user_dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">
    <div class="users-list" id="usersList">
        <h3 style="text-align:center;">Chats</h3>
        <?php
        // Fetch unique users who have chatted
        $chat_users = [];
        $stmt = $conn->prepare("
            SELECT DISTINCT u.id, u.name
            FROM users u
            JOIN contact_messages m ON (u.id = m.sender_id OR u.id = m.receiver_id)
            WHERE u.id != ? AND (m.sender_id=? OR m.receiver_id=?)
        ");
        $stmt->bind_param("iii",$user_id,$user_id,$user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()){
            $chat_users[$row['id']] = $row['name'];
        }
        $stmt->close();

        foreach($chat_users as $uid => $uname):
            $stmt_unread = $conn->prepare("SELECT COUNT(*) FROM contact_messages WHERE sender_id=? AND receiver_id=? AND is_read=0");
            $stmt_unread->bind_param("ii",$uid,$user_id);
            $stmt_unread->execute();
            $stmt_unread->bind_result($unread_count);
            $stmt_unread->fetch();
            $stmt_unread->close();
        ?>
        <div class="user-item" onclick="showChat(<?= $uid ?>, '<?= addslashes($uname) ?>')">
            <?= htmlspecialchars($uname) ?>
            <?php if($unread_count>0): ?>
                <span class="badge"><?= $unread_count ?></span>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="chat-box">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <h3 id="chatWith">Select a user to start chatting</h3>
            <button id="deleteChatBtn" onclick="deleteChat()" disabled 
                style="background:red;color:white;border:none;padding:5px 10px;border-radius:5px;cursor:pointer;">
                🗑️ Delete Chat
            </button>
        </div>

        <div class="message-area" id="messageArea"></div>
        <div class="message-input">
            <input type="text" id="msgInput" placeholder="Type a message..." disabled>
            <button onclick="sendMessage()" id="sendBtn" disabled>Send</button>
        </div>
    </div>
</div>

<script>
let currentChatUser = null;

function showChat(userId, userName){
    currentChatUser = userId;
    document.getElementById('chatWith').innerText = "Chat with " + userName;
    document.getElementById('msgInput').disabled = false;
    document.getElementById('sendBtn').disabled = false;
    document.getElementById('deleteChatBtn').disabled = false;

    fetch('fetch_messages.php?chat_user=' + userId)
    .then(res=>res.json())
    .then(data=>{
        const area = document.getElementById('messageArea');
        area.innerHTML = '';
        data.forEach(msg=>{
            let div = document.createElement('div');
            div.classList.add('message');
            div.classList.add(msg.sender_id == <?= $user_id ?> ? 'sent' : 'received');

            let deleteBtn = '';
            if (msg.sender_id == <?= $user_id ?>) {
                deleteBtn = `<button onclick="deleteMessage(${msg.message_id})" class="delete-btn">🗑️</button>`;
            }

            div.innerHTML = `
                ${deleteBtn}
                <strong>${msg.subject}</strong><br>
                ${msg.message}
                <div class='message-time'>${msg.created_at}</div>
            `;
            area.appendChild(div);
        });
        area.scrollTop = area.scrollHeight;

        // Remove unread badges
        const items = document.getElementsByClassName('user-item');
        for(let i=0;i<items.length;i++){
            items[i].querySelector('.badge')?.remove();
        }
    });
}

function sendMessage(){
    const msg = document.getElementById('msgInput').value;
    if(msg.trim()==='' || currentChatUser===null) return;
    const formData = new FormData();
    formData.append('receiver_id', currentChatUser);
    formData.append('message', msg);
    formData.append('subject', 'Reply');

    fetch('send_message.php',{method:'POST',body:formData})
    .then(res=>res.text())
    .then(res=>{
        document.getElementById('msgInput').value='';
        showChat(currentChatUser, document.getElementById('chatWith').innerText.replace('Chat with ',''));
    });
}

function deleteMessage(messageId) {
    if (!messageId || messageId <= 0) { alert("Invalid message ID"); return; }
    if (!confirm("Are you sure you want to delete this message?")) return;

    const formData = new FormData();
    formData.append('message_id', messageId);

    fetch('delete_message.php', {method:'POST', body: formData})
    .then(res => res.text())
    .then(res => {
        if(res.includes("successfully")){
            showChat(currentChatUser, document.getElementById('chatWith').innerText.replace('Chat with ',''));
        } else {
            alert("Error: " + res);
        }
    }).catch(err => alert("Network error: " + err));
}

function deleteChat(){
    if(currentChatUser === null) return;
    if(!confirm("Are you sure you want to delete this entire chat?")) return;

    fetch('delete_chat.php?chat_user=' + currentChatUser)
    .then(res => res.text())
    .then(res => {
        alert(res);
        document.getElementById('messageArea').innerHTML = '';
        document.getElementById('chatWith').innerText = 'Select a user to start chatting';
        document.getElementById('msgInput').disabled = true;
        document.getElementById('sendBtn').disabled = true;
        document.getElementById('deleteChatBtn').disabled = true;
    });
}

// Auto-refresh messages every 5 seconds
setInterval(()=>{
    if(currentChatUser) showChat(currentChatUser, document.getElementById('chatWith').innerText.replace('Chat with ',''));
},5000);
</script>

</body>
</html>
