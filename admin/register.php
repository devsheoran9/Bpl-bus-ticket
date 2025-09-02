<?php include_once('function/_db.php');
user_login_index_check();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>

        body ul li.parsley-type,
        body.dark-layout ul li.parsley-required,
        body.dark-layout ul li.parsley-maxlength,
        body.dark-layout ul li.parsley-length,
        body.dark-layout ul li.parsley-min,
        body.dark-layout ul li.parsley-range,
        body.dark-layout ul li.parsley-equalto,
        body.dark-layout ul li.parsley-minlength,
        body.dark-layout ul li.parsley-maxlength {
            font-size: 0.8rem;
            position: relative;
            top: 0rem;
            left: 0rem;
            -webkit-transform: translateY(0%);
            -moz-transform: translateY(0%);
            -ms-transform: translateY(0%);
            -o-transform: translateY(0%);
            transform: translateY(0%);
            color: #df7d7f !important;
            line-height: 14px;
            list-style-type: none;
            list-style-image: none;
        }
        body ul li.parsley-type,
        body ul li.parsley-required,
        body ul li.parsley-maxlength,
        body ul li.parsley-length,
        body ul li.parsley-min,
        body ul li.parsley-range,
        body ul li.parsley-equalto,
        body ul li.parsley-minlength,
        body ul li.parsley-max {
            font-size: 0.8rem;
            position: relative;
            top: 0rem;
            left: 0rem;
            -webkit-transform: translateY(0%);
            -moz-transform: translateY(0%);
            -ms-transform: translateY(0%);
            -o-transform: translateY(0%);
            transform: translateY(0%);
            color: #d61519 !important;
            line-height: 14px;
            list-style-type: none;
            list-style-image: none;
        }
        .parsley-error {
            border: #d61519 solid 1px !important;
        }
        .checkbox.parsley-error {
            border: 0px solid #d61519 !important;
        }
        .parsley-errors-list {
            padding: 5px 0px;
            margin-bottom: 0px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <h3 class="text-center">Register</h3>
            <form class="data-form" action="function/register.php">
                <div class="mb-3">
                    <label for="yourname" class="form-label">Your Name</label>
                    <input type="text" class="form-control" id="yourname" name="yourname" required>
                </div>
                <div class="mb-3">
                    <label for="mobile" class="form-label">Mobile No.</label>
                    <input type="text" class="form-control" id="mobile" name="mobile" minlength="10" maxlength="10" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" minlength="6" required>
                </div>
                <div class="d-grid">
                    <button type="submit" id="submit" class="btn btn-primary">Register</button>
                </div>
                <div class="d-grid mt-4">
                    <a href="index.php" class="btn btn-warning">Login Now</a>
                </div>
            </form>

        </div>
    </div>
</div>

<script src="assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/notify.js"></script>
<script src="assets/js/parsley.min.js"></script>
<script type="text/javascript">
    $('form').parsley();
    $(document).on('submit','form.data-form',function(e) {
        e.preventDefault();
        const action = $(this).attr('action');
        const m_form_data = new FormData(this);
        $('#submit').text('please wait...');
        if($(this).parsley().isValid()){
            $.ajax({
                type: "POST",
                url: action,
                data: m_form_data,
                cache: false,
                dataType:"json",
                enctype: 'multipart/form-data',
                processData: false,  //tell jQuery not to process the data
                contentType: false,   //tell jQuery not to set contentType
                success: function(data){
                    //alert(data);
                    let goTo ='';
                    const notify_type = data.notif_type;
                    const notify_title = data.notif_title;
                    const notify_desc = data.notif_desc;
                    if(notify_type) {
                        $.notify({
                            // options
                            title: notify_title,
                            message: notify_desc
                        }, {
                            // settings
                            type: notify_type
                        });
                    }
                    goTo = data.goTo;
                    if(data.res === 'true' && goTo !== ''){
                        window.location = goTo;
                    }
                },
                error: function (jqXHR, exception) {
                    var msg = '';
                    if (jqXHR.status === 0) {
                        msg = "Network Problem";
                    } else if (jqXHR.status === 404) {
                        msg = "404 error";
                    } else if (jqXHR.status === 500) {
                        msg = "505 error";
                    } else if (exception === 'parsererror') {
                        msg = "Data Error";
                    } else if (exception === 'timeout') {
                        msg = "Network Problem - Timeout";
                    } else if (exception === 'abort') {
                        msg = "Invalid Data Entery";
                    } else {
                        msg = "oops something want wrong"
                    }
                    $.notify({
                        // options
                        title: '(Please Retry) - ',
                        message: msg
                    },{
                        // settings
                        type: 'warning'
                    });
                }
            });
        }
    })
</script>
</body>
</html>
