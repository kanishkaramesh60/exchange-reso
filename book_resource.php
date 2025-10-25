<?php
session_start();
include 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Validate resource ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid resource ID.");
}
$resource_id = intval($_GET['id']);

try {
    // Begin transaction
    $conn->begin_transaction();

    // ✅ Fetch resource info (and lock it to prevent race booking)
    $stmt_resource = $conn->prepare("SELECT added_by, status, title FROM resources WHERE resource_id=? FOR UPDATE");
    $stmt_resource->bind_param("i", $resource_id);
    $stmt_resource->execute();
    $stmt_resource->bind_result($added_by, $status, $resource_title);
    $stmt_resource->fetch();
    $stmt_resource->close();

    // ✅ Prevent self-booking
    if ($added_by == $user_id) {
        throw new Exception("You cannot book your own resource.");
    }

    // ✅ Prevent booking already booked resource
    if ($status === 'booked') {
        throw new Exception("This resource is already booked.");
    }

    // ✅ Prevent duplicate booking by same user
    $stmt_check = $conn->prepare("SELECT * FROM bookings WHERE resource_id=? AND user_id=?");
    $stmt_check->bind_param("ii", $resource_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        throw new Exception("You have already booked this resource.");
    }
    $stmt_check->close();

    // ✅ Insert booking record
    $stmt_book = $conn->prepare("INSERT INTO bookings (resource_id, user_id) VALUES (?, ?)");
    $stmt_book->bind_param("ii", $resource_id, $user_id);
    $stmt_book->execute();
    $stmt_book->close();

    // ✅ Update resource status
    $stmt_update = $conn->prepare("UPDATE resources SET status='booked' WHERE resource_id=?");
    $stmt_update->bind_param("i", $resource_id);
    $stmt_update->execute();
    $stmt_update->close();

    // Insert internal notification (inbox) for the owner
$subject = "Your resource '{$resource_title}' has been booked!";
$msg_body = "Hello {$owner_name}, your resource '{$resource_title}' was booked by {$booked_by_name}.";

// Store in 'contact_messages' table
$stmt_notify = $conn->prepare("
    INSERT INTO contact_messages (sender_id, receiver_id, subject, message, created_at) 
    VALUES (?, ?, ?, ?, NOW())
");
$stmt_notify->bind_param("iiss", $user_id, $added_by, $subject, $msg_body);
$stmt_notify->execute();
$stmt_notify->close();


    // ✅ Fetch owner email, name + user info of who booked
    $stmt_owner = $conn->prepare("
        SELECT u.email, u.name 
        FROM users u 
        JOIN resources r ON u.id = r.added_by 
        WHERE r.resource_id=?
    ");
    $stmt_owner->bind_param("i", $resource_id);
    $stmt_owner->execute();
    $stmt_owner->bind_result($owner_email, $owner_name);
    $stmt_owner->fetch();
    $stmt_owner->close();

    // Get booking user's name for message
    $stmt_user = $conn->prepare("SELECT name FROM users WHERE id=?");
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($booked_by_name);
    $stmt_user->fetch();
    $stmt_user->close();

    // ✅ Create internal notification message (stored in DB)
    $subject = "Your resource '{$resource_title}' has been booked!";
    $msg_body = "Hello {$owner_name}, your resource titled '{$resource_title}' was booked by {$booked_by_name}.";
    $stmt_notify = $conn->prepare("INSERT INTO contact_messages (sender_id, receiver_id, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt_notify->bind_param("iiss", $user_id, $added_by, $subject, $msg_body);
    $stmt_notify->execute();
    $stmt_notify->close();

    // ✅ Commit transaction
    $conn->commit();

    // ✅ Send Email Notification to Owner
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'resourcex04@gmail.com';  // your sender email
        $mail->Password = 'ymij gbbq rslp zwwz';   // your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('resourcex04@gmail.com', 'ResourceX');
        $mail->addAddress($owner_email, $owner_name);

        $mail->isHTML(true);
        $mail->Subject = 'Your Resource Has Been Booked';
        $mail->Body = "
            Hello {$owner_name},<br><br>
            Your resource <strong>{$resource_title}</strong> has just been booked by <strong>{$booked_by_name}</strong>.<br>
            Please check your dashboard or inbox for booking details.<br><br>
            Regards,<br>
            <strong>ResourceX Team</strong>
        ";
        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
    }

    $_SESSION['message'] = "Resource booked successfully! The owner has been notified via email and inbox.";
    header("Location: user_dashboard.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['message'] = $e->getMessage();
    header("Location: user_dashboard.php");
    exit();
}

$conn->close();
?>
