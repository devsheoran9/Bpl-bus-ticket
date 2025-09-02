<?php
global $_conn_db;
include_once('function/_db.php');
check_user_login();

$name = $_SESSION['user']['name'];
$email = $_SESSION['user']['email'];
$mobile = $_SESSION['user']['mobile'];

$current_page = basename($_SERVER['PHP_SELF']);
$is_money_active = $is_transaction_active = $is_cash_active = $is_contact_active = '';
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <?php include_once('head.php');?>
        <style>
            .form-label { font-weight: 500; }
            .form-text { font-size: 12px; color: #888; }
        </style>
    </head>
    <body>
    <div id="wrapper">
        <?php include_once('sidebar.php');?>
        <div class="main-content">
            <?php include_once('header.php');?>
            <div class="container-fluid ">
                <h2 class="mb-4">Create SEO Optimized City Page</h2>
                <form id="seoForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="city" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="slug" class="form-label">Slug (URL part) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="slug" placeholder="packers-movers-in-delhi" required>
                            <div class="form-text">e.g. packers-movers-in-delhi</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="h1" class="form-label">H1 Tag</label>
                            <input type="text" class="form-control" name="h1" placeholder="Main heading for the page">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Meta Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                        <div class="form-text">Max 60 characters recommended.</div>
                    </div>
                    <div class="mb-3">
                        <label for="meta_description" class="form-label">Meta Description</label>
                        <textarea class="form-control" name="meta_description" rows="2"></textarea>
                        <div class="form-text">Max 160 characters recommended.</div>
                    </div>
                    <div class="mb-3">
                        <label for="keywords" class="form-label">Meta Keywords</label>
                        <input type="text" class="form-control" name="keywords">
                        <div class="form-text">Comma separated.</div>
                    </div>
                    <div class="mb-3">
                        <label for="canonical_url" class="form-label">Canonical URL</label>
                        <input type="url" class="form-control" name="canonical_url">
                    </div>
                    <div class="mb-3">
                        <label for="robots" class="form-label">Robots</label>
                        <select class="form-control" name="robots">
                            <option value="index,follow">index,follow</option>
                            <option value="noindex,follow">noindex,follow</option>
                            <option value="index,nofollow">index,nofollow</option>
                            <option value="noindex,nofollow">noindex,nofollow</option>
                        </select>
                        <div class="form-text">Control search engine indexing.</div>
                    </div>
                    <div class="mb-3">
                        <label for="sitemap" class="form-label">Include in Sitemap</label>
                        <select class="form-control" name="sitemap">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="og_title" class="form-label">OG Title</label>
                        <input type="text" class="form-control" name="og_title" placeholder="Open Graph Title for social sharing">
                    </div>
                    <div class="mb-3">
                        <label for="og_description" class="form-label">OG Description</label>
                        <textarea class="form-control" name="og_description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="og_image" class="form-label">OG Image URL</label>
                        <input type="url" class="form-control" name="og_image" placeholder="https://example.com/image.jpg">
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Full Page Content (HTML allowed) <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="content" rows="6" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="schema_json" class="form-label">Schema JSON</label>
                        <textarea class="form-control" name="schema_json" rows="6"></textarea>
                        <div class="form-text">Paste valid JSON-LD for structured data.</div>
                    </div>
                    <button class="btn btn-main-blue ladda-button p-1 p-1 submit-btn" data-style="zoom-in" type="submit" name="action"><span class="ladda-label">Save Page <i id="icon-arrow" class="bx bx-right-arrow-alt"></i></span> <span class="ladda-spinner"></span> </button>
                </form>
                <div id="response" class="mt-3"></div>
            </div>
        </div>
    </div>
    <?php include_once('foot.php');?>
    <script>
        $('#seoForm').submit(function(e) {
            e.preventDefault();
            $.ajax({
                url: 'function/insert/add_page.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(data) {
                    $('#response').html('<div class="alert alert-success">'+data+'</div>');
                    $('#seoForm')[0].reset();
                },
                error: function(xhr) {
                    $('#response').html('<div class="alert alert-danger">Error: '+xhr.responseText+'</div>');
                }
            });
        });
    </script>
    </body>
    </html>
<?php pdo_close_conn($_conn_db); ?>