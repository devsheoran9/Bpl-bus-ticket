<?php
global $_conn_db;
include_once('function/_db.php'); // सुनिश्चित करें कि यह पाथ सही है
check_user_login(); // सुनिश्चित करें कि यह फ़ंक्शन परिभाषित है और रीडायरेक्शन को संभालता है

$name = $_SESSION['user']['name'] ?? 'Guest';
$email = $_SESSION['user']['email'] ?? '';
$mobile = $_SESSION['user']['mobile'] ?? '';

$current_page = basename($_SERVER['PHP_SELF']);

$bus_id = htmlspecialchars($_GET['bus_id'] ?? '');

// बस_आईडी का मज़बूत सत्यापन (Robust bus_id validation)
if (empty($bus_id) || !is_numeric($bus_id)) {
    // बस_आईडी गायब या अमान्य होने पर एरर दिखाएं या रीडायरेक्ट करें
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Invalid Request';
    $_SESSION['notif_desc'] = 'No bus ID provided or invalid bus ID.';
    header("Location: bus_list.php"); // उदाहरण: बस सूची पेज पर रीडायरेक्ट करें
    exit();
}

// डिस्प्ले के लिए बस का नाम लाएं
$bus_name_display = "Bus ID: " . $bus_id;
try {
    $stmt = $_conn_db->prepare("SELECT bus_name FROM buses WHERE bus_id = ?");
    $stmt->execute([$bus_id]);
    $bus_info = $stmt->fetch(PDO::FETCH_ASSOC); // एसोसिएटिव एरे के रूप में लाएं
    if ($bus_info) {
        $bus_name_display = htmlspecialchars($bus_info['bus_name']) . " (ID: " . $bus_id . ")";
    } else {
        // बस_आईडी नहीं मिली, रीडायरेक्ट करें
        $_SESSION['notif_type'] = 'error';
        $_SESSION['notif_title'] = 'Bus Not Found';
        $_SESSION['notif_desc'] = 'The requested bus could not be found.';
        header("Location: bus_list.php");
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching bus name for bus_id $bus_id: " . $e->getMessage());
    $_SESSION['notif_type'] = 'error';
    $_SESSION['notif_title'] = 'Database Error';
    $_SESSION['notif_desc'] = 'Could not retrieve bus information due to a database error.';
    header("Location: bus_list.php"); // DB एरर होने पर बस सूची पेज पर वापस जाएं
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Seats for <?php echo $bus_name_display; ?></title>
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/ladda/ladda.min.css" rel="stylesheet">
<link href="assets/css/style.css?<?php echo time();?>" rel="stylesheet">
<link href="assets/fa-font/css/all.min.css" rel="stylesheet">
<!-- Summernote CSS - आपको इसकी आवश्यकता होगी -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<!-- Boxicons CSS -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

<style>
     
    h2 {
        text-align: center;
        margin-bottom: 40px; /* हेडिंग के नीचे अधिक जगह */
        color: #212529; /* हेडिंग के लिए गहरा टेक्स्ट */
        font-weight: 700;
        letter-spacing: -0.5px;
    }
    .form-group {
        margin-bottom: 25px; /* सुसंगत स्पेसिंग */
    }
    label {
        font-weight: 600;
        margin-bottom: 10px; /* लेबल और इनपुट के बीच अधिक जगह */
        display: block;
        color: #343a40; /* गहरे लेबल टेक्स्ट */
    }
    .form-control {
        border-radius: 6px; /* थोड़े अधिक गोल इनपुट */
        padding: 12px 18px; /* इनपुट के अंदर अधिक पैडिंग */
        border: 1px solid #ced4da; /* हल्का बॉर्डर */
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06); /* सूक्ष्म आंतरिक छाया */
        transition: border-color 0.2s ease, box-shadow 0.2s ease; /* चिकनी ट्रांजीशन */
    }
    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); /* बूटस्ट्रैप-जैसा फोकस ग्लो */
        outline: none;
    }
    textarea.form-control {
        min-height: 150px; /* लम्बे टेक्स्टएरिया */
        resize: vertical;
    }
    .form-text {
        font-size: 0.875em; /* थोड़ा बड़ा छोटा टेक्स्ट */
        color: #6c757d; /* म्यूटेड टेक्स्ट कलर */
        margin-top: 6px;
    }
 .custom-file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 6px;
        background-color: #f8f9fa;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .custom-file-input-wrapper:hover {
        border-color: #a0aec0;
        box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
    }
    .custom-file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        z-index: 2;
    }
    .custom-file-upload-button {
        flex-shrink: 0;
        padding: 12px 18px;
        background-color: #007bff;
        color: white;
        border-radius: 6px 0 0 6px;
        display: flex;
        text-align: center;
        justify-content: center;
        align-items: center;
        gap: 8px;
        width:100%;
        font-weight: 500;
    }
    .custom-file-upload-button i {
        font-size: 1.1em;
    }
    .custom-file-upload-button:hover {
        background-color: #0056b3;
    }
    .custom-file-input-display {
        flex-grow: 1;
        /* ADD THIS LINE */
        min-width: 0; /* Allows the flex item to shrink below its content size */
        /* END ADD */
        padding: 12px 18px;
        color: #495057;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font-style: italic;
    }
    /* Image Preview Styling */
    .image-preview-container {
        margin-top: 20px;
        padding: 15px;
        background-color: #e9ecef; /* हल्का ग्रे बैकग्राउंड */
        border: 1px dashed #adb5bd; /* विज़ुअल सेपरेशन के लिए डैश वाला बॉर्डर */
        border-radius: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 15px; /* आइटम्स के बीच अधिक जगह */
        min-height: 80px; /* लम्बा प्लेसहोल्डर */
        align-items: center;
        justify-content: flex-start;
        position: relative; /* क्लियर/रिमूव बटन की पोजिशनिंग के लिए */
    }
    .image-preview-container.empty-state::before {
        content: "No images selected";
        color: #868e96;
        font-style: italic;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }

    /* For single featured image preview */
    .featured-image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        display: block;
    }
    .featured-image-preview {
        position: relative; /* क्लियर बटन के लिए */
        padding-right: 30px; /* क्लियर बटन के लिए जगह */
        width: 200px; /* सुनिश्चित करें कि यह कंटेनर को भरता है */
    }
    .featured-image-preview .clear-featured-image {
        position: absolute;
        top: 0;
        right: 0;
        background-color: #dc3545; /* डिलीट के लिए लाल */
        color: white;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 1.2em;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .featured-image-preview .clear-featured-image:hover {
        background-color: #c82333;
        transform: scale(1.1);
    }

    /* For multiple image thumbnails */
    .image-preview-item {
        position: relative;
        width: 120px; /* थंबनेल के लिए बड़ा निश्चित आकार */
        height: 120px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: #f1f3f5; /* आइटम के लिए हल्का बैकग्राउंड */
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); /* आइटम शैडो */
    }
    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* फिट करने के लिए क्रॉप करें, पहलू अनुपात बनाए रखें */
    }
    .image-preview-item .remove-image-button {
        position: absolute;
        top: 5px;
        right: 5px;
        background-color: rgba(220, 53, 69, 0.8); /* पारदर्शिता के साथ लाल */
        color: white;
        border: none;
        border-radius: 50%;
        width: 25px;
        height: 25px;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        z-index: 10;
        transition: background-color 0.2s ease;
    }
    .image-preview-item .remove-image-button:hover {
        background-color: #dc3545; /* होवर पर ठोस लाल */
    }

    /* Parsley.js Error Styling */
    .parsley-error-list {
        color: #dc3545;
        font-size: 0.875em;
        margin-top: 8px;
        list-style: none; /* बुलेट पॉइंट हटाएं */
        padding-left: 0;
    }
    .form-control.parsley-error {
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    /* Summernote specific styling to match form controls */
    .note-editor.note-frame {
        border-radius: 6px;
        border: 1px solid #ced4da;  
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
    }
    .note-toolbar {
        border-bottom: 1px solid #ced4da;
    }

    /* Submit Button Styling */
    .btn-primary {
        background-color: #28a745; /* सफलता/सबमिट के लिए हरा */
        border-color: #28a745;
        padding: 12px 25px;
        font-size: 1.1em;
        font-weight: 600;
        border-radius: 6px;
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    .btn-primary:hover {
        background-color: #218838;
        border-color: #1e7e34;
        box-shadow: 0 6px 15px rgba(40, 167, 69, 0.3);
    }
    .btn-primary:active {
        background-color: #1e7e34;
        border-color: #1c7430;
        box-shadow: 0 2px 5px rgba(40, 167, 69, 0.4);
        transform: translateY(1px);
    }
</style>
<style>
        /* Optional: Basic styling for fieldsets */
        .form-label{
            margin-bottom:0;
        }
        .form-label{
            font-size:13px;
        }
        fieldset {
            border: 1px solid #ddd !important;
            padding: 0.25rem !important;
            margin-bottom: 2rem;
            border-radius: .25rem;
        }
        legend {
            font-size: 1.2em !important;
            font-weight: bold !important;
            text-align: left !important;
            width: auto; /* सामग्री के आधार पर ऑटो चौड़ाई के लिए */
            padding: 0 10px; /* टेक्स्ट के चारों ओर पैडिंग */
            border-bottom: none;
        }
        .text-danger {
            color: #dc3545; /* बूटस्ट्रैप का खतरा रंग */
        }
        .form-control{
            padding:0.25rem;
        }
        
        
        .mini{
            font-size:10px;
        }
    </style>
    <style>
        /* एक सुसंगत ग्रिड आकार परिभाषित करें। यह JS में GRID_SIZE से मेल खाना चाहिए। */
        :root {
            --grid-size: 10px; 
            --seat-color-available: #34C759; /* इमेज से हरा */
            --seat-color-sold: #F0F0F0; /* इमेज से हल्का ग्रे */
            --seat-color-male: #BBDEFB; /* इमेज से हल्का नीला */
            --seat-color-female: #FFCDD2; /* इमेज से हल्का गुलाबी */
            --seat-color-any: #E0E0E0; /* न्यूट्रल ग्रे */
            --seat-text-color-sold: #888;
        }

        .card{
            max-width: 301px; /* पैलेट और समरी कार्ड के लिए निश्चित चौड़ाई */
        }
        .deck-container {
            position: relative;
            min-height: 700px; /* बेहतर दृश्यता के लिए न्यूनतम ऊंचाई बढ़ाई गई */
            border: 1px solid #ccc;
            background-color: #fcfcfc; /* डेक के लिए थोड़ा हल्का बैकग्राउंड */
            overflow: auto; /* बाहर खींची गई किसी भी चीज़ को छुपाएं और आवश्यकतानुसार स्क्रॉलिंग सक्षम करें */
            /* संरेखण के लिए विज़ुअल ग्रिड */
            max-width: 300px; /* डेक के लिए निश्चित अधिकतम चौड़ाई */
            width: 100%; /* फ्लेक्स कंटेनर में सिकुड़ने की अनुमति दें */
            background-image: linear-gradient(to right, #eee 1px, transparent 1px),
                              linear-gradient(to bottom, #eee 1px, transparent 1px);
            background-size: var(--grid-size) var(--grid-size); 
            margin-bottom: 20px;
            border-radius: 12px; /* इमेज से मैच करने के लिए गोल कोने */
        }
        .seat-palette {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .seat-template, .seat {
            position: relative; /* आंतरिक तत्वों की पोजिशनिंग के लिए आवश्यक */
            display: flex; /* सामग्री को केंद्र में रखने के लिए फ्लेक्स का उपयोग करें */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 0.7em; /* छोटा टेक्स्ट */
            font-weight: 600;
            color: #333; /* डिफ़ॉल्ट टेक्स्ट कलर */
            background-color: var(--seat-color-any); /* डिफ़ॉल्ट बैकग्राउंड */
            border: 1px solid #ccc; /* डिफ़ॉल्ट बॉर्डर */
            border-radius: 8px; /* सभी के लिए थोड़े गोल कोने */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden; /* ओवरफ्लो को छुपाएं */
            cursor: grab;
            transition: all 0.1s ease-out; /* चिकनी ट्रांजीशन */
            touch-action: none; /* ब्राउज़र टच क्रियाओं को खींचने में बाधा डालने से रोकें */
            box-sizing: border-box; /* पैडिंग/बॉर्डर को चौड़ाई/ऊंचाई में शामिल करें */
            
            /* डिफ़ॉल्ट आयाम (2 * GRID_SIZE) */
            width: calc(var(--grid-size) * 4); /* 40px */
            height: calc(var(--grid-size) * 4); /* 40px */
        }
        /* डेक पर सीटें पूर्ण रूप से स्थित हैं */
        .seat {
            position: absolute; 
        }
        /* सीट टेम्प्लेट को पूर्ण स्थिति की आवश्यकता नहीं है, वे फ्लेक्स आइटम हैं */
        .seat-template {
            position: static;
        }
        /* सीटर और स्लीपर सीटों के लिए ऊपर की ओर अधिक गोलाई */
        .seat.seater, .seat.sleeper {
            border-top-left-radius: calc(var(--grid-size) * 0.6); /* 6px */
            border-top-right-radius: calc(var(--grid-size) * 0.6); /* 6px */
            border-bottom-left-radius: calc(var(--grid-size) * 0.2); /* 2px */
            border-bottom-right-radius: calc(var(--grid-size) * 0.2); /* 2px */
        }
        .seat.sleeper { /* ऊंचाई समायोजित करें */
            height: calc(var(--grid-size) * 8); /* 80px */
        }
        .seat.driver { /* गोलाकार, स्टीयरिंग व्हील आइकॉन */
            width: calc(var(--grid-size) * 5); /* 50px */
            height: calc(var(--grid-size) * 5); /* 50px */
            border-radius: 50%;
            background-color: #6c757d;
            border-color: #5a6268;
            color: #fff;
            font-size: 1.2em;
        }
        .seat.aisle { /* सूक्ष्म बैकग्राउंड, डैश वाला बॉर्डर */
            background-color: transparent;
            border: 1px dashed #bbb;
            color: #888;
            font-style: italic;
            font-size: 0.6em;
            box-shadow: none;
        }
        .seat.aisle i {
            font-size: 1em;
            margin-bottom: 0; /* आइल टेक्स्ट के साथ आइकॉन की मार्जिन हटाएँ */
        }

        /* सीट के आंतरिक तत्व */
        .seat-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
            pointer-events: none; /* सीट div पर ड्रैग की अनुमति देने के लिए महत्वपूर्ण */
            text-align: center; /* टेक्स्ट को भी केंद्र में रखें */
            padding: 2px; /* थोड़ी पैडिंग */
        }
        .seat-icon {
            font-size: 1.1em;
            margin-bottom: 2px;
            line-height: 1; /* बेहतर आइकॉन संरेखण के लिए */
            pointer-events: none; /* आइकॉन को क्लिक में बाधा डालने से रोकें */
        }
        .seat-text { /* "Sold" जैसे टेक्स्ट के लिए */
            font-size: 0.7em;
            font-weight: 600;
            line-height: 1;
            pointer-events: none;
        }
        .seat-price {
            font-size: 0.8em;
            font-weight: 700;
            color: var(--seat-color-available); /* हरे रंग का मूल्य */
            line-height: 1;
            pointer-events: none;
        }
        /* यदि आप संपादक के लिए सीट कोड दिखाना चाहते हैं */
        .seat-code { 
            position: absolute;
            top: 2px;
            left: 2px;
            font-size: 0.6em;
            color: #777;
            pointer-events: none;
            opacity: 0.7;
        }

        /* सीट की स्थिति के आधार पर क्लासें */
        /* उपलब्ध, बुक करने योग्य (हरी आउटलाइन) */
        .seat.status-available.bookable {
            border-color: var(--seat-color-available);
            background-color: #fff; /* उपलब्ध के लिए सफेद आंतरिक भाग */
        }

        /* सोल्ड या अनुपलब्ध (ग्रे, "Sold" टेक्स्ट) */
        .seat.status-sold { /* इसमें DAMAGED, BLOCKED, या is_bookable=false शामिल है */
            background-color: var(--seat-color-sold);
            border-color: var(--seat-color-sold);
            color: var(--seat-text-color-sold);
        }
        .seat.status-sold .seat-price {
             color: var(--seat-text-color-sold);
        }

        /* लिंग प्राथमिकता के रंग */
        .seat-icon.gender-male {
            color: #1a73e8; /* आइकॉन के लिए गहरा नीला */
        }
        .seat-icon.gender-female {
            color: #e91e63; /* आइकॉन के लिए गहरा गुलाबी */
        }
        .seat-icon.gender-any {
            color: #888; /* न्यूट्रल आइकॉन कलर */
        }

        /* संपादन के लिए चयनित - मूल पीली आउटलाइन रखें */
        .seat.selected-for-edit {
            border: 2px solid #ffc107;
            box-shadow: 0 0 10px rgba(255,193,7,0.8);
            z-index: 100;
        }
        /* खींची गई सहायक वस्तु के लिए शैली */
        .ui-draggable-dragging {
            z-index: 1000; /* सुनिश्चित करें कि खींचा गया आइटम शीर्ष पर है */
            opacity: 0.8;
            /* सुनिश्चित करें कि सहायक में सही बॉक्स मॉडल और आयाम हैं */
            position: absolute !important; 
            margin: 0 !important; 
            padding: 0 !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3) !important;
        }

        /* स्टीयरिंग व्हील आइकॉन के लिए विशेष स्टाइलिंग */
        .steering-icon-header {
            font-size: 1.4em; /* हेडर में आइकॉन को थोड़ा बड़ा करें */
            color: #6c757d;
        }
        .steering-icon-header i {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<div id="wrapper">
    <?php include_once('sidebar.php');?>
    <div class="main-content">
        <?php include_once('header.php');?>
        <div class="container-fluid ">
            <h2 class="mb-4 mt-4 text-center">Manage Seat Layout for <span id="current_bus_name"><?php echo $bus_name_display; ?></span></h2>

            <div class="row">
                <div class="col-lg-3 col-md-4 mb-4">
                <div class="card">
    <div class="card-header">Seat Palette</div>
    <div class="card-body seat-palette">
        <!-- डेटा-डिफ़ॉल्ट-चौड़ाई और डेटा-डिफ़ॉल्ट-ऊंचाई को GRID_SIZE के गुणांक के रूप में सेट करें -->
        <!-- अब आइकॉन का उपयोग कर रहे हैं, और होवर पर नाम के लिए 'title' एट्रिब्यूट -->
        <div class="seat-template seater" data-seat-type="SEATER" data-base-price="350" data-orientation="VERTICAL" data-default-width="40" data-default-height="40" title="Seater">
            <i class="fas fa-chair"></i>
        </div>
        <div class="seat-template sleeper" data-seat-type="SLEEPER" data-base-price="800" data-orientation="VERTICAL" data-default-width="40" data-default-height="80" title="Sleeper">
            <i class="fas fa-bed"></i>
        </div>
        <div class="seat-template driver bg-light" data-seat-type="DRIVER" data-is-bookable="false" data-default-width="50" data-default-height="50" title="Driver">
            <i class="fas fa-steering-wheel" ></i>
        </div>
        <div class="seat-template aisle" data-seat-type="AISLE" data-is-bookable="false" data-default-width="40" data-default-height="40" title="Aisle/Gap">
            <i class="fas fa-arrows-alt-h"></i>
        </div>
        <hr>
        <div class="d-grid gap-2">
            <button type="button" class="btn btn-warning btn-sm delete-selected-seat-btn w-100"><i class="fas fa-trash"></i> Delete Selected Seat</button>
        </div>
    </div>
</div>

                    <div class="card mt-4">
                        <div class="card-header">Seat Summary</div>
                        <div class="card-body">
                            <p>Total Seats: <span id="total_seat_count">0</span></p>
                            <p>Bookable Seats: <span id="bookable_seat_count">0</span></p>
                            <p>Seaters: <span id="seater_count">0</span></p>
                            <p>Sleepers: <span id="sleeper_count">0</span></p>
                            <p>Aisles/Gaps: <span id="aisle_count">0</span></p>
                            <p>Drivers: <span id="driver_count">0</span></p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8">
                    <!-- अन्य पेजों से form.data-form foot.php में मौजूद है। 
                         manage_seats के लिए, हम इसे स्पष्ट रूप से छुपाते हैं और bus_id के लिए एक अलग छिपा हुआ इनपुट का उपयोग करते हैं। -->
                    <form class="data-form" action="bus_actions.php" method="POST" data-parsley-validate style="display: none;">
                        <input type="hidden" name="action" value="dummy_save_action">
                        <input type="hidden" name="bus_id" value="<?php echo $bus_id; ?>">
                    </form>
                    
                    <!-- इस विशिष्ट पेज के JS में स्पष्टता के लिए bus_id के लिए अलग इनपुट -->
                    <input type="hidden" id="bus_id_for_js" value="<?php echo $bus_id; ?>">

                    <!-- डेक के लिए फ्लेक्स कंटेनर -->
                    <div class="d-flex flex-column flex-md-row gap-4 align-items-stretch"> <!-- align-items-stretch जोड़ा गया -->
                        <div class="card mb-4 flex-grow-1"> <!-- flex-grow-1 इसे बराबर जगह लेने के लिए बनाता है -->
                            <div class="card-header">Lower Deck <span class="float-end steering-icon-header"><i class="fas fa-steering-wheel"></i> Driver</span></div>
                            <div class="card-body deck-container" id="lower_deck_container">
                                <!-- लोअर डेक की सीटें यहाँ गतिशील रूप से जोड़ी जाएंगी -->
                            </div>
                        </div>

                        <div class="card mb-4 flex-grow-1">
                            <div class="card-header">Upper Deck</div>
                            <div class="card-body deck-container" id="upper_deck_container">
                                <!-- अपर डेक की सीटें यहाँ गतिशील रूप से जोड़ी जाएंगी -->
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <!-- यह बटन विज़ुअल फीडबैक देता है कि परिवर्तन गतिशील रूप से सहेजे गए हैं -->
                        <button type="button" class="btn btn-primary ladda-button p-1 submit-btn btn-lg" data-style="zoom-in" id="dynamicSaveNotificationBtn"><span class="ladda-label"><i class="bx bx-check-circle"></i> Changes Saved Dynamically</span> <span class="ladda-spinner"></span> </button>
                         <small class="text-muted text-center mt-2">सीट लेआउट परिवर्तन (जोड़ना, संपादित करना, हटाना, स्थानांतरित करना) तुरंत डेटाबेस में सहेजे जाते हैं।</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- सीट प्रॉपर्टी एडिटर मोडल -->
<div class="modal fade" id="seatPropertiesModal" tabindex="-1" aria-labelledby="seatPropertiesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seatPropertiesModalLabel">Edit Seat Properties (<span id="modal_display_seat_id"></span>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="seat-properties-form">
                    <input type="hidden" id="modal_seat_db_id"> <!-- छिपा हुआ DB सीट ID -->
                    <div class="mb-3">
                        <label for="modal_seat_code" class="form-label">Seat Code</label>
                        <input type="text" class="form-control" id="modal_seat_code" disabled> <!-- सीट कोड जनरेट/फिक्स्ड है -->
                    </div>
                    <div class="mb-3">
                        <label for="modal_seat_type" class="form-label">Seat Type</label>
                        <select class="form-select" id="modal_seat_type" disabled> <!-- निर्माण के बाद सीट प्रकार नहीं बदलना चाहिए -->
                            <option value="SEATER">Seater</option>
                            <option value="SLEEPER">Sleeper</option>
                            <option value="DRIVER">Driver</option>
                            <option value="AISLE">Aisle/Gap</option>
                            <option value="TOILET">Toilet</option>
                            <option value="GANGWAY">Gangway</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_base_price" class="form-label">Base Price</label>
                        <input type="number" class="form-control" id="modal_base_price" step="0.01" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="modal_gender_preference" class="form-label">Gender Preference</label>
                        <select class="form-select" id="modal_gender_preference">
                            <option value="ANY">Any</option>
                            <option value="MALE">Male</option>
                            <option value="FEMALE">Female</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="modal_is_bookable">
                        <label class="form-check-label" for="modal_is_bookable">Is Bookable</label>
                    </div>
                    <div class="mb-3">
                        <label for="modal_orientation" class="form-label">Orientation</label>
                        <select class="form-select" id="modal_orientation">
                            <option value="VERTICAL">Vertical</option>
                            <option value="HORIZONTAL">Horizontal</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="modal_width" class="form-label">Width (px)</label>
                        <input type="number" class="form-control" id="modal_width" min="10" step="10"> <!-- ग्रिड के साथ स्नैप -->
                    </div>
                    <div class="mb-3">
                        <label for="modal_height" class="form-label">Height (px)</label>
                        <input type="number" class="form-control" id="modal_height" min="10" step="10"> <!-- ग्रिड के साथ स्नैप -->
                    </div>
                     <div class="mb-3">
                        <label for="modal_status" class="form-label">Seat Status</label>
                        <select class="form-select" id="modal_status">
                            <option value="AVAILABLE">Available</option>
                            <option value="DAMAGED">Damaged</option>
                            <option value="BLOCKED">Blocked</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSeatProperties">Save changes</button>
            </div>
        </div>
    </div>
</div>
<?php
 
?>
<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/notify.js"></script>
<script src="assets/js/parsley.min.js"></script>
<script src="assets/ladda/spin.min.js"></script>
<script src="assets/ladda/ladda.min.js"></script>
<!-- SweetAlert2 (आधुनिक संस्करण) जोड़ा गया -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- jQuery UI (ड्रैगेबल/ड्रॉपपेबल के लिए आवश्यक) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

<script>
    // सामान्य फ़ॉर्म सबमिशन हैंडलर (यदि कोई हो तो अन्य फ़ॉर्म के लिए रखा गया है)
    $(document).on('submit','form.data-form',function(e) {
        e.preventDefault();
        // यदि यह manage_seats.php पर डमी फ़ॉर्म है, तो कुछ न करें।
        // manage_seats.php पर Ladda बटन को अलग से हैंडल किया जाता है।
        if ($(this).find('input[name="action"]').val() === 'dummy_save_action') {
             return; 
        }

        var action = $(this).attr('action');
        var m_form_data = new FormData(this);
        if($(this).parsley().isValid()){
            var submitBtn = $(this).find('.submit-btn')[0];
            var l;
            if (submitBtn) {
                l = Ladda.create(submitBtn);
                l.start();
            }

            $.ajax({
                type: "POST",
                url: action,
                data: m_form_data,
                cache: false,
                dataType:"json",
                enctype: 'multipart/form-data',
                processData: false,
                contentType: false,
                success: function(data){
                    setTimeout(function(){
                        if (l) l.stop();
                    }, 2500);
                    var goTo ='';
                    var notify_type = data.notif_type; 
                    var notify_title = data.notif_title;
                    var notify_desc = data.notif_desc;
                    var notif_popup = data.notif_popup;

                    // SweetAlert2 (Swal.fire) का उपयोग करें
                    if(notif_popup === 'true'){
                        Swal.fire({
                            title: notify_title,
                            text: notify_desc,
                            icon: notify_type 
                        });
                    }else{
                        $.notify({
                            title: notify_title,
                            message: notify_desc
                        },{
                            type: notify_type
                        });
                    }

                    goTo = data.goTo;
                    if(data.res === 'true' && goTo !== ''){
                        window.setTimeout(function(){
                            if(goTo === '469bba0a564235dfceede42db14f17b0'){ // यह मैजिक स्ट्रिंग एक विशेष 'वापस जाओ' क्रिया का तात्पर्य है
                                history.go(-1);
                            }else {
                                window.location.href = goTo;
                            }
                        }, 1000);
                        // यदि सीट प्रबंधन पर रीडायरेक्ट कर रहे हैं तो फॉर्म रीसेट न करें, क्योंकि हमें bus_id की आवश्यकता है
                        if (!goTo.includes('manage_seats.php')) {
                            $(this)[0].reset();
                            if ($(this).parsley()) {
                                $(this).parsley().reset();
                            }
                        }
                    } else if (data.res === 'true') {
                        // यदि सफल है लेकिन कोई रीडायरेक्ट नहीं है, तो बस नोटिफिकेशन दिखाएं और फॉर्म रीसेट करें
                        $(this)[0].reset();
                        if ($(this).parsley()) {
                            $(this).parsley().reset();
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = "Network Problem. Please check your internet connection.";
                    } else if (jqXHR.status == 404) {
                        msg = "Requested page not found. [404]";
                    } else if (jqXHR.status == 500) {
                        msg = "Internal Server Error. [500]";
                    } else if (exception === 'parsererror') {
                        msg = "Error parsing JSON response. The server response might be malformed.";
                    } else if (exception === 'timeout') {
                        msg = "Request timed out.";
                    } else if (exception === 'abort') {
                        msg = "Request aborted.";
                    } else {
                        msg = "Uncaught Error.\n" + jqXHR.responseText;
                    }
                    if (l) l.stop();
                    $.notify({
                        title: '(Please Retry) - ',
                        message: msg
                    },{
                        type: 'warning'
                    });
                }
            });
        }
    })
</script>

<script>
    ///साइडबार
    document.addEventListener('DOMContentLoaded', () => {
        const wrapper = document.getElementById('wrapper');
        const toggle  = document.getElementById('sidebarToggle');
        const icon    = document.getElementById('sidebarToggleIcon');

        let overlay = document.getElementById('sidebarOverlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = 'sidebarOverlay';
            wrapper.appendChild(overlay);
        }

        function handleToggleFor1080() {
            if (window.innerWidth >= 1080) {
                wrapper.classList.add('toggled');
            } else {
                wrapper.classList.remove('toggled');
            }
        }

        function updateToggleIcon() {
            if (!icon) return;
            if (wrapper.classList.contains('toggled')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }

        function handleResize() {
            handleToggleFor1080();
            updateToggleIcon();
        }

        handleResize(); // प्रारंभिक लोड पर कॉल करें

        if (toggle) { // सुनिश्चित करें कि टॉगल तत्व मौजूद है
            toggle.addEventListener('click', () => {
                wrapper.classList.toggle('toggled');
                updateToggleIcon();
            });
        }

        if (overlay) { // सुनिश्चित करें कि ओवरले तत्व मौजूद है
            overlay.addEventListener('click', () => {
                if (wrapper.classList.contains('toggled')) {
                    wrapper.classList.remove('toggled');
                    updateToggleIcon();
                }
            });
        }

        window.addEventListener('resize', handleResize);
    });

    function setupFileInput(inputElement, previewContainerId, fileNameDisplayId, isMultiple = false) {
        const previewContainer = $(`#${previewContainerId}`);
        const fileNameDisplay = $(`#${fileNameDisplayId}`);

        if (!inputElement.length || !previewContainer.length || !fileNameDisplay.length) {
            console.warn(`File input setup elements not found for: ${inputElement.selector}`);
            return; // यदि तत्व मौजूद नहीं हैं तो बाहर निकलें
        }

        if (previewContainer.children().length === 0) {
            previewContainer.addClass('empty-state');
        } else {
            previewContainer.removeClass('empty-state');
        }

        $(inputElement).on('change', function(event) {
            previewContainer.html('');
            previewContainer.removeClass('empty-state');

            if (event.target.files && event.target.files.length > 0) {
                if (!isMultiple) {
                    const file = event.target.files[0];
                    fileNameDisplay.text(file.name);

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = $('<img>').attr('src', e.target.result).addClass('image-preview');
                        const clearButton = $('<button type="button" class="clear-featured-image"><i class="fas fa-times"></i></button>');
                        
                        previewContainer.append(img).append(clearButton);

                        clearButton.on('click', function() {
                            inputElement.val('');
                            fileNameDisplay.text('No file chosen');
                            previewContainer.html('').addClass('empty-state');
                        });
                    };
                    reader.readAsDataURL(file);
                } else {
                    fileNameDisplay.text(`${event.target.files.length} files chosen`);
                    Array.from(event.target.files).forEach((file, index) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const itemDiv = $('<div>').addClass('image-preview-item');
                            const img = $('<img>').attr('src', e.target.result);
                            const removeButton = $('<button type="button" class="remove-image-button"><i class="fas fa-times"></i></button>');
                            
                            itemDiv.append(img).append(removeButton);
                            previewContainer.append(itemDiv);

                            removeButton.on('click', function() {
                                $(this).parent().remove();
                                if (previewContainer.children().length === 0) {
                                    fileNameDisplay.text('No files chosen');
                                    inputElement.val('');
                                    previewContainer.addClass('empty-state');
                                }
                            });
                        };
                        reader.readAsDataURL(file);
                    });
                }
            } else {
                fileNameDisplay.text(isMultiple ? 'No files chosen' : 'No file chosen');
                previewContainer.addClass('empty-state');
            }
        });
    }

    $(document).ready(function() {
        // ये तत्व सभी पेजों पर मौजूद नहीं हो सकते हैं, इसलिए setupFileInput को कॉल करने से पहले उनकी जांच करें
        if ($('#featured_image').length) {
            setupFileInput($('#featured_image'), 'featured_image_preview', 'featured_image_name', false);
        }
        if ($('#multiple_images').length) {
            setupFileInput($('#multiple_images'), 'multiple_images_preview', 'multiple_images_name', true);
        }

        if ($('#long_description').length) {
            $('#long_description').summernote({
                placeholder: 'Write your blog content here...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
    });

</script>

<!-- कस्टम सीट मैनेजमेंट जावास्क्रिप्ट (केवल manage_seats.php के लिए) -->
<script>
    // यह स्क्रिप्ट केवल manage_seats.php पर चलनी चाहिए
    if (window.location.pathname.includes('manage_seats.php')) {
        $(document).ready(function() {
            const busId = $('#bus_id_for_js').val(); // समर्पित छिपे हुए इनपुट से bus_id प्राप्त करें
            let currentSeats = []; // DB से लाई गई सभी सीट ऑब्जेक्ट्स को रखने के लिए एरे
            let selectedSeatElement = null; // वर्तमान में चयनित सीट DIV के jQuery ऑब्जेक्ट को संग्रहीत करता है
            const GRID_SIZE = 10; // CSS में --grid-size से मेल खाना चाहिए

            let seatCodeCounter = { // नई सीटों के लिए अद्वितीय seat_code जनरेट करने के लिए
                LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1,
                UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1
            };

            // गतिशील सेव नोटिफिकेशन के लिए Ladda बटन
            const dynamicSaveLadda = Ladda.create($('#dynamicSaveNotificationBtn')[0]);
            $('#dynamicSaveNotificationBtn').on('click', function() {
                dynamicSaveLadda.start();
                setTimeout(function() {
                    dynamicSaveLadda.stop();
                    $.notify({
                        title: 'Info',
                        message: 'All changes have been saved automatically to the database.'
                    }, {
                        type: 'info'
                    });
                }, 1000);
            });


            function initializeSeatCodeCounter() {
                seatCodeCounter = { // पुनः गणना से पहले काउंटरों को रीसेट करें
                    LOWER_SEATER: 1, LOWER_SLEEPER: 1, LOWER_AISLE: 1,
                    UPPER_SEATER: 1, UPPER_SLEEPER: 1, UPPER_AISLE: 1
                };
                // प्रत्येक प्रकार/डेक के लिए अधिकतम मौजूदा संख्या ढूंढकर अद्वितीय सीट कोड सुनिश्चित करें
                currentSeats.forEach(seat => {
                    const deckPrefix = seat.deck.substring(0,1); // L या U
                    if (seat.seat_type === 'SEATER' && seat.seat_code.startsWith(deckPrefix + 'S')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'S', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_SEATER']) seatCodeCounter[seat.deck + '_SEATER'] = num + 1;
                    } else if (seat.seat_type === 'SLEEPER' && seat.seat_code.startsWith(deckPrefix + 'P')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'P', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_SLEEPER']) seatCodeCounter[seat.deck + '_SLEEPER'] = num + 1;
                    } else if (seat.seat_type === 'AISLE' && seat.seat_code.startsWith(deckPrefix + 'G')) {
                        const num = parseInt(seat.seat_code.replace(deckPrefix + 'G', ''));
                        if (!isNaN(num) && num >= seatCodeCounter[seat.deck + '_AISLE']) seatCodeCounter[seat.deck + '_AISLE'] = num + 1;
                    }
                });
                console.log("Seat code counters initialized:", seatCodeCounter); // डीबगिंग
            }

            function updateSeatCounts() {
                let total = 0, bookable = 0, seater = 0, sleeper = 0, aisle = 0, driver = 0;
                currentSeats.forEach(seat => {
                    total++;
                    if (seat.is_bookable) { 
                        bookable++;
                    }
                    switch(seat.seat_type) {
                        case 'SEATER': seater++; break;
                        case 'SLEEPER': sleeper++; break;
                        case 'AISLE': aisle++; break;
                        case 'DRIVER': driver++; break;
                        // TOILET और GANGWAY को आमतौर पर उपलब्ध सीटों के रूप में नहीं गिना जाता है
                        // case 'TOILET': break; 
                        // case 'GANGWAY': break;
                    }
                });
                $('#total_seat_count').text(total);
                $('#bookable_seat_count').text(bookable); 
                $('#seater_count').text(seater);
                $('#sleeper_count').text(sleeper);
                $('#aisle_count').text(aisle);
                $('#driver_count').text(driver);
                console.log("Seat counts updated."); // डीबगिंग
            }

            function loadSeatsForBus() {
                if (!busId || busId === 'N/A') {
                    $.notify({ title: 'Error', message: 'Invalid Bus ID. Cannot load seats.' }, { type: 'danger' });
                    renderSeats();
                    updateSeatCounts();
                    return;
                }
                console.log("Attempting to load seats for bus ID:", busId); // डीबगिंग

                $.ajax({
                    url: 'function/backend/bus_actions.php', // सुनिश्चित करें कि यह पाथ सही है
                    type: 'GET',
                    dataType: 'json',
                    data: { action: 'get_bus_seats', bus_id: busId },
                    success: function(response) {
                        console.log("AJAX success for get_bus_seats. Raw response:", response); // रॉ रिस्पांस डीबग करना
                        if (response.res === 'true' && response.seats) {
                            currentSeats = response.seats;
                            // महत्वपूर्ण: is_bookable को '0'/'1' स्ट्रिंग से बूलियन में बदलें
                            currentSeats.forEach(seat => {
                                // सुनिश्चित करें कि सभी न्यूमेरिक फ़ील्ड गणना के लिए वास्तविक संख्याएं हैं
                                seat.seat_id = parseInt(seat.seat_id);
                                seat.bus_id = parseInt(seat.bus_id);
                                seat.x_coordinate = parseInt(seat.x_coordinate);
                                seat.y_coordinate = parseInt(seat.y_coordinate);
                                seat.width = parseInt(seat.width);
                                seat.height = parseInt(seat.height);
                                seat.base_price = parseFloat(seat.base_price);
                                // सुनिश्चित करें कि is_bookable एक बूलियन है
                                seat.is_bookable = (seat.is_bookable === 1 || seat.is_bookable === '1');
                            });
                            console.log("Current seats array after type conversion:", currentSeats); // परिवर्तित डेटा डीबग करना
                        } else {
                            $.notify({ title: 'Info', message: 'No existing seats found for this bus. Starting with a blank layout.' }, { type: 'info' });
                            currentSeats = [];
                        }
                        initializeSeatCodeCounter(); // मौजूदा सीटें लोड होने के बाद काउंटरों को इनिशियलाइज़ करें
                        renderSeats(); // लोड की गई सीटों को प्रदर्शित करने के लिए renderSeats को कॉल करें
                    },
                    error: function(xhr, status, error) {
                        $.notify({ title: 'Error', message: 'Failed to load seats from server: ' + status + ' ' + error + ". Server Response: " + xhr.responseText }, { type: 'danger' });
                        console.error("AJAX error loading seats:", xhr.responseText);
                        currentSeats = []; // असंगत स्थिति को रोकने के लिए एरर पर वर्तमान सीटों को साफ़ करें
                        initializeSeatCodeCounter();
                        renderSeats(); // फिर भी खाली डेक दिखाने के लिए रेंडर करें
                    }
                });
            }

            function renderSeats() {
                $('#lower_deck_container').empty();
                $('#upper_deck_container').empty();
                console.log("Rendering " + currentSeats.length + " seats..."); // डीबगिंग

                currentSeats.forEach(seatData => {
                    const containerId = `${seatData.deck.toLowerCase()}_deck_container`;
                    const container = $(`#${containerId}`);

                    if (!container.length) {
                        console.warn(`Container with ID ${containerId} not found for seat_id ${seatData.seat_id}. Skipping render.`);
                        return; // यदि कंटेनर मौजूद नहीं है तो छोड़ दें
                    }

                    const seatElement = createSeatElement(seatData);
                    
                    // मौजूदा सीटों के लिए, उनके संग्रहीत कोऑर्डिनेट्स का सीधे उपयोग करें
                    // x_coordinate और y_coordinate पहले से ही GRID_SIZE पर स्नैप होने चाहिए जब वे सहेजे गए थे
                    seatElement.css({
                        left: seatData.x_coordinate + 'px',
                        top: seatData.y_coordinate + 'px',
                        width: seatData.width + 'px',
                        height: seatData.height + 'px',
                        transform: seatData.orientation === 'HORIZONTAL' ? 'rotate(90deg)' : 'none'
                    });
                    console.log(`Appending seat_id ${seatData.seat_id} to ${containerId} at X:${seatData.x_coordinate}, Y:${seatData.y_coordinate}`); // प्रत्येक सीट को डीबग करना
                    container.append(seatElement);
                });
                makeSeatsDraggableAndEditable();
                updateSeatCounts(); // रेंडरिंग के बाद काउंट अपडेट करें
            }

            function createSeatElement(seatData) {
                const element = $('<div>')
                    .addClass('seat')
                    .addClass(seatData.seat_type.toLowerCase())
                    .attr('id', 'seat_' + seatData.seat_id) // DOM तत्व ID के लिए DB seat_id का उपयोग करें
                    .data('seat-data', seatData);

                // सीट की स्थिति क्लासें लागू करें
                if (seatData.is_bookable && seatData.status === 'AVAILABLE') {
                    element.addClass('status-available bookable');
                } else {
                    element.addClass('status-sold'); 
                }

                // सीट की सामग्री बनाएं और जोड़ें
                element.append(createSeatElementContent(seatData));

                // संपादक में क्षैतिज स्लीपर के लिए रोटेशन हैंडल करें
                // CSS ट्रांसफॉर्म प्रॉपर्टी पहले ही लागू हो चुकी है, यह यहाँ केवल डेटा के आधार पर एक दोहराव है
                // ताकि रेंडर के बाद भी तत्व के विज़ुअल रोटेशन की स्थिति बनी रहे।
                // CSS में भी यह स्टाइल `.seat.sleeper` के लिए लागू होती है।
                if (seatData.orientation === 'HORIZONTAL') {
                     // इसका मतलब है कि CSS width और height को आपस में बदल दिया गया है, लेकिन हम यहां सीधे CSS ट्रांसफॉर्म पर निर्भर करते हैं
                }

                return element;
            }

            function makeSeatsDraggableAndEditable() {
                $('.seat').draggable({
                    // containment को बस के दोनों डेक को कवर करने वाले कंटेनर में बदल दिया गया है
                    containment: ".d-flex.flex-md-row", 
                    grid: [GRID_SIZE, GRID_SIZE], // ग्रिड पर स्नैप करें
                    stack: ".seat", // खींची गई सीट को अन्य सीटों के सामने लाएं
                    stop: function(event, ui) {
                        const seat_id = $(this).data('seat-data').seat_id;
                        // ड्रैगेबल के `stop` पर, `ui.position` में पैरेंट कंटेनर के सापेक्ष स्नैप की गई स्थिति होती है।
                        // हमें इसे फिर से उसके डेक-कंटेनर के सापेक्ष बनाना होगा।
                        const $this = $(this);
                        const $deckContainer = $this.closest('.deck-container');
                        
                        let new_x = ui.offset.left - $deckContainer.offset().left;
                        let new_y = ui.offset.top - $deckContainer.offset().top;

                        // ग्रिड पर फिर से स्नैप करें ताकि सटीक हो
                        new_x = Math.round(new_x / GRID_SIZE) * GRID_SIZE;
                        new_y = Math.round(new_y / GRID_SIZE) * GRID_SIZE;

                        // सुनिश्चित करें कि यह डेक कंटेनर के भीतर रहता है (यदि कोई हो)
                        new_x = Math.max(0, Math.min(new_x, $deckContainer.width() - $this.outerWidth()));
                        new_y = Math.max(0, Math.min(new_y, $deckContainer.height() - $this.outerHeight()));
                        
                        $this.css({ left: new_x, top: new_y }); // DOM को अपडेट करें

                        // निरंतरता के लिए तुरंत currentSeats एरे में अपडेट करें
                        const seatIndex = currentSeats.findIndex(s => s.seat_id == seat_id); // ढीली तुलना के लिए == का उपयोग करें यदि DB स्ट्रिंग देता है
                        if (seatIndex > -1) {
                            currentSeats[seatIndex].x_coordinate = new_x;
                            currentSeats[seatIndex].y_coordinate = new_y;
                            // DB में स्थिति अपडेट करने के लिए AJAX भेजें
                            saveSeatToDB({seat_id: seat_id, x_coordinate: new_x, y_coordinate: new_y, bus_id: busId}, 'update_seat_position');
                        }
                    }
                }).on('click', function(e) {
                    e.stopPropagation(); // कंटेनर पर क्लिक को डीसेलेक्ट करने से रोकें
                    $('.seat').removeClass('selected-for-edit');
                    $(this).addClass('selected-for-edit');
                    selectedSeatElement = $(this);
                    editSeatProperties($(this).data('seat-data'));
                });

                // कंटेनर पर क्लिक करने से सीट डीसेलेक्ट हो जाती है
                $('.deck-container').on('click', function() {
                    $('.seat').removeClass('selected-for-edit');
                    selectedSeatElement = null;
                });
            }

            function editSeatProperties(seatData) {
                $('#modal_display_seat_id').text(seatData.seat_code); // seat_code प्रदर्शित करें
                $('#modal_seat_db_id').val(seatData.seat_id); // DB ID के लिए छिपा हुआ फ़ील्ड
                $('#modal_seat_code').val(seatData.seat_code); // मूल सीट कोड (संपादन के लिए अक्षम)
                $('#modal_seat_type').val(seatData.seat_type); // निर्माण के बाद प्रकार आमतौर पर नहीं बदला जाता है
                $('#modal_base_price').val(seatData.base_price || 0);
                $('#modal_gender_preference').val(seatData.gender_preference || 'ANY');
                $('#modal_is_bookable').prop('checked', seatData.is_bookable); // सीधे बूलियन का उपयोग करें
                $('#modal_orientation').val(seatData.orientation || 'VERTICAL');
                $('#modal_width').val(seatData.width || (seatData.seat_type === 'DRIVER' ? 50 : 40));
                $('#modal_height').val(seatData.height || (seatData.seat_type === 'SLEEPER' ? 80 : (seatData.seat_type === 'DRIVER' ? 50 : 40)));
                $('#modal_status').val(seatData.status || 'AVAILABLE');


                const isConfigurableBookable = (seatData.seat_type !== 'DRIVER' && seatData.seat_type !== 'AISLE' && seatData.seat_type !== 'TOILET' && seatData.seat_type !== 'GANGWAY');
                
                // केवल विशिष्ट सीट प्रकारों के लिए मूल मूल्य, लिंग प्राथमिकता, बुक करने योग्य स्थिति और ओरिएंटेशन को संपादित करने की अनुमति दें
                $('#modal_base_price, #modal_gender_preference, #modal_is_bookable, #modal_orientation, #modal_status, #modal_width, #modal_height').prop('disabled', !isConfigurableBookable);
                
                // ड्राइवर/आयल/टॉयलेट/गैंगवे में कुछ निश्चित गुण हो सकते हैं जिन्हें मोडल से संपादित नहीं किया जा सकता है
                if (seatData.seat_type === 'DRIVER' || seatData.seat_type === 'AISLE' || seatData.seat_type === 'TOILET' || seatData.seat_type === 'GANGWAY') {
                    $('#modal_is_bookable').prop('checked', false).prop('disabled', true); // हमेशा गैर-बुक करने योग्य
                    $('#modal_base_price').val(0).prop('disabled', true); // हमेशा 0 मूल्य
                    $('#modal_gender_preference').val('ANY').prop('disabled', true); // हमेशा ANY प्राथमिकता
                }

                $('#seatPropertiesModal').modal('show');
            }

            $('#saveSeatProperties').on('click', function() {
                if (selectedSeatElement) {
                    const seat_db_id = $('#modal_seat_db_id').val();
                    const currentSeatData = selectedSeatElement.data('seat-data'); // मर्ज करने के लिए वर्तमान डेटा प्राप्त करें
                    
                    const updatedSeatData = {
                        ...currentSeatData, // मौजूदा डेटा जैसे कोऑर्डिनेट्स को बनाए रखें यदि स्पष्ट रूप से नहीं बदला गया है
                        seat_id: seat_db_id, 
                        bus_id: busId, // सुनिश्चित करें कि bus_id हमेशा मौजूद है
                        base_price: parseFloat($('#modal_base_price').val()),
                        gender_preference: $('#modal_gender_preference').val(),
                        is_bookable: $('#modal_is_bookable').prop('checked'),
                        orientation: $('#modal_orientation').val(),
                        width: parseInt($('#modal_width').val()),
                        height: parseInt($('#modal_height').val()),
                        status: $('#modal_status').val(),
                        // Coordinates are updated by draggable, use current element position.
                        x_coordinate: selectedSeatElement.position().left,
                        y_coordinate: selectedSeatElement.position().top
                    };
                    
                    // AJAX के माध्यम से डेटाबेस में अपडेट करें
                    saveSeatToDB(updatedSeatData, 'update_seat', function(response) {
                        if (response.res === 'true') {
                            // स्थानीय currentSeats एरे को अपडेट करें
                            const seatIndex = currentSeats.findIndex(s => s.seat_id == seat_db_id);
                            if (seatIndex > -1) {
                                currentSeats[seatIndex] = updatedSeatData; // पुराने को अपडेटेड से बदलें
                            }
                            
                            // DOM तत्व को अपडेट करें
                            selectedSeatElement.data('seat-data', updatedSeatData); // jQuery डेटा अपडेट करें
                            
                            // क्लासें अपडेट करें
                            selectedSeatElement.removeClass('status-available bookable status-sold');
                            if (updatedSeatData.is_bookable && updatedSeatData.status === 'AVAILABLE') {
                                selectedSeatElement.addClass('status-available bookable');
                            } else {
                                selectedSeatElement.addClass('status-sold');
                            }

                            selectedSeatElement.css({
                                width: updatedSeatData.width + 'px',
                                height: updatedSeatData.height + 'px',
                                transform: updatedSeatData.orientation === 'HORIZONTAL' ? 'rotate(90deg)' : 'none'
                            });
                            
                            // सामग्री को फिर से रेंडर करें ताकि आइकॉन/मूल्य/टेक्स्ट अपडेट हो सकें
                            selectedSeatElement.html(''); // पुरानी सामग्री साफ़ करें
                            selectedSeatElement.append(createSeatElementContent(updatedSeatData)); // नई सामग्री जोड़ें

                            updateSeatCounts(); // काउंट की पुनः गणना करें
                        }
                        $('#seatPropertiesModal').modal('hide');
                        selectedSeatElement = null; // सहेजने के बाद डीसेलेक्ट करें
                        $('.seat').removeClass('selected-for-edit'); // हाईलाइट हटाएं
                    });
                }
            });

            // यह फ़ंक्शन createSeatElement से सामग्री निर्माण को अलग करता है
            // ताकि हम सीट के CSS/data को बदले बिना केवल आंतरिक HTML को अपडेट कर सकें
            function createSeatElementContent(seatData) {
                const seatContent = $('<div>').addClass('seat-content');
                let iconHtml = '';
                let priceHtml = '';
                let seatTextHtml = '';

                switch (seatData.seat_type) {
                    case 'SEATER':
                    case 'SLEEPER':
                        let genderClass = 'gender-any';
                        let genderIcon = 'fas fa-user';
                        if (seatData.gender_preference === 'MALE') {
                            genderClass = 'gender-male';
                            genderIcon = 'fas fa-male';
                        } else if (seatData.gender_preference === 'FEMALE') {
                            genderClass = 'gender-female';
                            genderIcon = 'fas fa-female';
                        }
                        iconHtml = `<i class="seat-icon ${genderIcon} ${genderClass}"></i>`;

                        if (seatData.is_bookable && seatData.status === 'AVAILABLE') {
                            priceHtml = `<span class="seat-price">₹${seatData.base_price.toFixed(0)}</span>`;
                        } else {
                            seatTextHtml = `<span class="seat-text">Sold</span>`;
                        }
                        break;
                    case 'DRIVER':
                        iconHtml = '<i class="fas fa-steering-wheel"></i>';
                        break;
                    case 'AISLE':
                        seatTextHtml = '<span class="seat-text"><i class="fas fa-arrows-alt-h"></i> Aisle</span>';
                        break;
                    case 'TOILET':
                        iconHtml = '<i class="fas fa-toilet"></i>';
                        break;
                    case 'GANGWAY':
                        iconHtml = '<i class="fas fa-walking"></i>';
                        break;
                }

                if (iconHtml) seatContent.append($(iconHtml));
                if (seatTextHtml) seatContent.append($(seatTextHtml));
                if (priceHtml) seatContent.append($(priceHtml));

                // seatContent.append($(`<span class="seat-code">${seatData.seat_code}</span>`)); // यदि आप सीट कोड दिखाना चाहते हैं

                return seatContent;
            }

            $('.seat-template').draggable({
                helper: 'clone',
                revert: 'invalid', // अमान्य लक्ष्य पर नहीं गिराए जाने पर पैलेट पर वापस लौटें
                cursor: 'grabbing',
                start: function(event, ui) {
                    // गिराए जाने पर वास्तविक सीट आयामों से मेल खाने के लिए हेल्पर आयाम सेट करें
                    const defaultWidth = $(this).data('default-width');
                    const defaultHeight = $(this).data('default-height');
                    const originalOrientation = $(this).data('original-orientation') || 'VERTICAL';

                    let helperWidth = defaultWidth;
                    let helperHeight = defaultHeight;
                    let helperTransform = 'none';

                    // यदि क्षैतिज है, तो हेल्पर के आयामों को इंटरचेंज करें और ट्रांसफॉर्म लागू करें
                    if (originalOrientation === 'HORIZONTAL') {
                        helperWidth = defaultHeight; // चौड़ाई को डिफ़ॉल्ट ऊंचाई पर सेट करें
                        helperHeight = defaultWidth;  // ऊंचाई को डिफ़ॉल्ट चौड़ाई पर सेट करें
                        helperTransform = 'rotate(90deg)';
                    }


                    $(ui.helper).css({
                        'z-index': 1000,
                        'opacity': 0.8, /* हेल्पर के लिए थोड़ी कम अपारदर्शिता */
                        'background-image': 'none', // हेल्पर से ग्रिड पैटर्न हटाएं
                        'width': helperWidth + 'px', // डेटा विशेषता से चौड़ाई सेट करें
                        'height': helperHeight + 'px', // डेटा विशेषता से ऊंचाई सेट करें
                        'position': 'absolute', // सुनिश्चित करें कि हेल्पर सही ढंग से स्थित है
                        'margin': 0, // कोई भी डिफ़ॉल्ट मार्जिन रीसेट करें
                        'padding': 0, // कोई भी डिफ़ॉल्ट पैडिंग रीसेट करें
                        'transform': helperTransform // हेल्पर पर ट्रांसफॉर्म लागू करें
                    }).data({
                        'original-type': $(this).data('seat-type'),
                        'original-price': $(this).data('base-price'),
                        'original-is-bookable': $(this).data('is-bookable') !== false, // बूलियन में बदलें
                        'original-orientation': originalOrientation, // निर्धारित ओरिएंटेशन का उपयोग करें
                        'default-width': defaultWidth, // मूल डिफ़ॉल्ट आयामों को डेटा में रखें
                        'default-height': defaultHeight
                    });
                     // हेल्पर के लिए भी सीट कंटेंट को क्लोन करें
                    const tempSeatDataForHelper = { // अस्थायी डेटा ऑब्जेक्ट
                        seat_type: $(this).data('seat-type'),
                        base_price: $(this).data('base-price') || 0,
                        gender_preference: 'ANY', // या डिफ़ॉल्ट जो भी हो
                        is_bookable: $(this).data('is-bookable') !== false,
                        status: 'AVAILABLE',
                        seat_code: $(this).text().trim() || 'NEW' // सीट कोड पैलेट टेक्स्ट से
                    };
                    $(ui.helper).append(createSeatElementContent(tempSeatDataForHelper));
                    // हेल्पर पर उचित क्लास भी जोड़ें ताकि यह सही दिखे
                    $(ui.helper)
                        .addClass($(this).data('seat-type').toLowerCase())
                        .addClass('status-available bookable'); // नए हेल्पर को "उपलब्ध" मानें
                }
            });

            $('.deck-container').droppable({
                accept: '.seat-template',
                hoverClass: 'bg-light',
                drop: function(event, ui) {
                    const newSeatType = ui.helper.data('original-type');
                    const deckId = $(this).attr('id').replace('_deck_container', '').toUpperCase();
                    const deckPrefix = deckId.substring(0, 1);

                    let newSeatCode;
                    let defaultWidth = ui.helper.data('default-width');
                    let defaultHeight = ui.helper.data('default-height');
                    let isBookable = ui.helper.data('original-is-bookable');

                    if (newSeatType === 'SEATER') {
                        newSeatCode = `${deckPrefix}S${seatCodeCounter[deckId + '_SEATER']++}`;
                    } else if (newSeatType === 'SLEEPER') {
                        newSeatCode = `${deckPrefix}P${seatCodeCounter[deckId + '_SLEEPER']++}`;
                    } else if (newSeatType === 'DRIVER') {
                        newSeatCode = 'DRIVER';
                        isBookable = false; // ड्राइवर सीट कभी बुक करने योग्य नहीं होती
                        // जांचें कि इस बस के लिए इस डेक पर ड्राइवर सीट पहले से मौजूद है या नहीं
                        if (currentSeats.some(s => s.seat_type === 'DRIVER' && s.deck === deckId)) {
                            $.notify({ title: 'Warning', message: 'Driver seat already exists on the ' + deckId.toLowerCase() + ' deck.' }, { type: 'warning' });
                            return; // दूसरी ड्राइवर सीट न जोड़ें
                        }
                    } else if (newSeatType === 'AISLE') {
                        newSeatCode = `${deckPrefix}G${seatCodeCounter[deckId + '_AISLE']++}`;
                        isBookable = false; // आइल कभी बुक करने योग्य नहीं होता
                    } else {
                        newSeatCode = `${deckPrefix}X${Math.floor(Math.random() * 10000)}`; // अज्ञात प्रकारों के लिए फ़ॉलबैक
                    }

                    // ui.offset ड्रैग के अंत में helper की दस्तावेज़ के सापेक्ष सटीक स्थिति है।
                    // $(this).offset() droppable कंटेनर की दस्तावेज़ के सापेक्ष स्थिति है।
                    // इन दोनों के अंतर से droppable के सापेक्ष top-left प्राप्त होता है।
                    let snappedX = event.pageX - $(this).offset().left;
                    let snappedY = event.pageY - $(this).offset().top;

                    // हेल्पर का आधा width/height घटाएं ताकि mouse pointer सीट के केंद्र में हो (यह आपकी इमेज के डिज़ाइन से मेल खाता है जहाँ आइकॉन केंद्र में हैं)
                    // यदि आप चाहते हैं कि ड्रॉप हमेशा शीर्ष-बाएं कोने से हो, तो इन लाइनों को कमेंट करें।
                    snappedX -= ui.helper.width() / 2;
                    snappedY -= ui.helper.height() / 2;
                    
                    // ग्रिड पर स्नैप करें
                    snappedX = Math.round(snappedX / GRID_SIZE) * GRID_SIZE; 
                    snappedY = Math.round(snappedY / GRID_SIZE) * GRID_SIZE;
                    
                    // सुनिश्चित करें कि कोऑर्डिनेट्स नकारात्मक न हों
                    snappedX = Math.max(0, snappedX);
                    snappedY = Math.max(0, snappedY);

                    console.log("Dropped (event.pageX, Y):", event.pageX, event.pageY);
                    console.log("Droppable offset:", $(this).offset().left, $(this).offset().top);
                    console.log("Calculated relative pos (before snap):", event.pageX - $(this).offset().left, event.pageY - $(this).offset().top);
                    console.log("Helper dimensions:", ui.helper.width(), ui.helper.height());
                    console.log("Snapped to:", snappedX, snappedY);

                    const newSeatData = {
                        bus_id: busId,
                        seat_code: newSeatCode,
                        deck: deckId,
                        seat_type: newSeatType,
                        x_coordinate: snappedX,
                        y_coordinate: snappedY,
                        width: defaultWidth,
                        height: defaultHeight,
                        orientation: ui.helper.data('original-orientation'),
                        base_price: ui.helper.data('original-price') || 0,
                        gender_preference: 'ANY',
                        is_bookable: isBookable,
                        status: 'AVAILABLE'
                    };

                    // AJAX के माध्यम से डेटाबेस में जोड़ें
                    saveSeatToDB(newSeatData, 'add_seat', function(response) {
                        if (response.res === 'true') {
                            newSeatData.seat_id = parseInt(response.new_seat_id); // DB द्वारा जनरेट किया गया ID प्राप्त करें, सुनिश्चित करें कि यह एक संख्या है
                            currentSeats.push(newSeatData); // स्थानीय एरे में जोड़ें
                            renderSeats(); // नए को शामिल करने और काउंट अपडेट करने के लिए सभी सीटों को फिर से रेंडर करें
                        } else {
                            // यदि DB ऐड विफल हो गया तो सीट कोड को पुनः उपयोग करने के लिए काउंटर को कम करें
                            if (newSeatType === 'SEATER') seatCodeCounter[deckId + '_SEATER']--;
                            else if (newSeatType === 'SLEEPER') seatCodeCounter[deckId + '_SLEEPER']--;
                            else if (newSeatType === 'AISLE') seatCodeCounter[deckId + '_AISLE']--;
                            console.error("Failed to add seat, decrementing counter:", newSeatCode); // डीबगिंग
                        }
                    });
                }
            });

            // सीट क्रियाओं के लिए सामान्य AJAX फ़ंक्शन
            function saveSeatToDB(seatData, actionType, callback) {
                // सुनिश्चित करें कि bus_id हमेशा शामिल है
                seatData.bus_id = busId; // सभी seatData पेलोड में busId जोड़ें
                seatData.action = actionType;

                $.ajax({
                    url: 'function/backend/bus_actions.php', // सुनिश्चित करें कि यह पाथ सही है
                    type: 'POST',
                    dataType: 'json',
                    data: seatData,
                    success: function(response) {
                        if (response.res === 'true') {
                            $.notify({ title: 'Success', message: response.notif_desc }, { type: 'success' });
                            if (callback) callback(response);
                        } else {
                            // उन एररों के लिए SweetAlert2 का उपयोग करें जो अधिक गंभीर हो सकते हैं या उपयोगकर्ता की कार्रवाई की आवश्यकता हो सकती है
                            Swal.fire({
                                title: response.notif_title || 'Operation Failed',
                                text: response.notif_desc || 'Please try again.',
                                icon: response.notif_type || 'error'
                            });
                            if (callback) callback(response); // UI में एरर हैंडलिंग के लिए भी कॉलबैक को कॉल करें
                        }
                    },
                    error: function(xhr, status, error) {
                        const errorMsg = `Failed to ${actionType.replace('_', ' ')}: ` + status + ' ' + error + ". Server Response: " + xhr.responseText;
                        $.notify({ title: 'Error', message: errorMsg }, { type: 'danger' });
                        console.error(`AJAX error on ${actionType}:`, xhr.responseText);
                        if (callback) callback({res: 'false', notif_desc: 'Network or server error.'}); // विफलता इंगित करें
                    }
                });
            }

            $('.delete-selected-seat-btn').on('click', function() {
                if (selectedSeatElement) {
                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "No, cancel!" 
                    }).then((result) => { 
                        if (result.isConfirmed) { 
                            const seatIdToDelete = selectedSeatElement.data('seat-data').seat_id;
                            saveSeatToDB({ seat_id: seatIdToDelete, bus_id: busId }, 'delete_seat', function(response) {
                                if (response.res === 'true') {
                                    currentSeats = currentSeats.filter(seat => seat.seat_id != seatIdToDelete); // ढीली तुलना के लिए != का उपयोग करें
                                    selectedSeatElement.remove();
                                    selectedSeatElement = null;
                                    Swal.fire("Deleted!", "Your seat has been deleted.", "success");
                                    initializeSeatCodeCounter(); // डिलीट करने के बाद काउंटरों की पुनः गणना करें
                                    updateSeatCounts(); // डिलीट करने के बाद काउंट अपडेट करें
                                } else {
                                    Swal.fire("Error!", "Failed to delete seat: " + (response.notif_desc || 'Unknown error.'), "error");
                                }
                            });
                        }
                    });
                } else {
                    $.notify({ title: 'Info', message: 'Please select a seat to delete.' }, { type: 'info' });
                }
            });

            // पेज लोड होने पर सीट लेआउट का प्रारंभिक लोड
            loadSeatsForBus();
        });
    }
</script>
</body>
</html>
<?php pdo_close_conn($_conn_db); ?>