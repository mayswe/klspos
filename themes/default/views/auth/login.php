<?php (defined('BASEPATH')) OR exit('No direct script access allowed'); ?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title . ' | ' . $Settings->site_name; ?></title>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= $assets ?>dist/css/styles.css" rel="stylesheet" type="text/css"/>
    <?= $Settings->rtl ? '<link href="' . $assets . 'dist/css/rtl.css" rel="stylesheet" />' : ''; ?>
    <style>
        body {
            background: #f5f7fa;
        }

        .login-box {
            width: 420px;
            max-width: calc(100% - 32px);
            margin: 7% auto;
            padding: 24px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border-radius: 8px;
        }

        .login-logo img {
            max-width: 180px;
        }

        .google-btn {
            background: #dd4b39;
            color: white;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            display: block;
            width: 100%;
            text-align: center;
            transition: background 0.3s ease;
        }

        .google-btn:hover {
            background: #c23321;
            text-decoration: none;
            color: #fff;
        }

        .login-box-msg {
            font-size: 16px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .login-divider { display: flex; align-items: center; gap: 12px; margin: 22px 0; color: #777; }
        .login-divider::before, .login-divider::after { content: ''; flex: 1; border-top: 1px solid #ddd; }
        .login-submit { width: 100%; padding: 12px; }
        .forgot-password { display: block; margin-top: 16px; text-align: center; }
        #forgot_password { display: none; margin-top: 24px; border-top: 1px solid #ddd; padding-top: 20px; }
        #forgot_password:target { display: block; }
    </style>
</head>
<body class="login-page login-page-<?= $Settings->theme_style; ?> rtl rtl-inv">
<div class="login-box">
    <div class="login-logo">
        <a href="<?= base_url(); ?>">
            <?= $Settings->site_name == 'SimplePOS' ? 'Simple<b>POS</b>' : '<img src="' . base_url('uploads/' . $Settings->logo) . '" alt="' . $Settings->site_name . '" />'; ?>
        </a>
    </div>

    <div class="login-box-body">
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php } if ($message) { ?>
            <div class="alert alert-success"><?= $message; ?></div>
        <?php } ?>

        <p class="login-box-msg">Sign in to continue</p>

        <?= form_open('auth/login'); ?>
            <div class="form-group">
                <label for="identity">Username / Email</label>
                <input type="text" name="identity" id="identity" class="form-control" autocomplete="username" value="<?= set_value('identity'); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" autocomplete="current-password" required>
            </div>
            <?php if ($Settings->captcha) { ?>
                <div class="form-group">
                    <?= $image; ?>
                    <label for="captcha"><?= lang('captcha'); ?></label>
                    <?= form_input($captcha); ?>
                </div>
            <?php } ?>
            <div class="checkbox"><label><input type="checkbox" name="remember" value="1"> Remember me</label></div>
            <button type="submit" class="btn btn-primary login-submit">Login</button>
        <?= form_close(); ?>
        <a href="#forgot_password" class="forgot-password">Forgot Password?</a>
        <div class="login-divider">OR</div>

        <a href="<?= base_url('auth/login_with_google'); ?>" class="google-btn">
            <i class="fa fa-google"></i> &nbsp;Login with Google
        </a>
        <section id="forgot_password" aria-labelledby="forgot-title">
            <h4 id="forgot-title">Reset your password</h4>
            <p>Enter your registered email to receive reset instructions.</p>
            <?= form_open('auth/forgot_password'); ?>
                <div class="form-group">
                    <label for="forgot_email">Email</label>
                    <input type="email" id="forgot_email" name="forgot_email" class="form-control" autocomplete="email" required>
                </div>
                <button type="submit" class="btn btn-primary login-submit">Send reset email</button>
            <?= form_close(); ?>
            <a href="#" class="forgot-password">Back to login</a>
        </section>
    </div>
</div>

<script src="<?= $assets ?>plugins/jQuery/jQuery-2.1.4.min.js"></script>
<script src="<?= $assets ?>bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
