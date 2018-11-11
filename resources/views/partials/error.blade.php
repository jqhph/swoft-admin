<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>{{ $title }} | {{ config('admin.name') }}</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

    <style>
        * {margin: 0px;padding: 0px;}
        html, body, div, h1, h2, h3, ul, ol, li, dt, p, table, th, td, img {
            margin: 0;
            padding: 0;
            border: none;
        }
        body {
            width: 100%;
            height: 100%;
            background: #fff;
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
            color: #636b6f;
            font-weight: normal;
            font-style: normal;
        }
        a:link, a:hover, a:visited {
            color: #007ee5 !important;
            text-decoration: none;
        }
        .rotate {
            -o-transform: rotate(-10deg);
            -moz-transform: rotate(-10deg);
            -webkit-transform: rotate(-10deg);
            -ms-transform: rotate(-10deg);
        }
        .social {
            margin: 50px auto;
            width: 408px;
            padding-bottom: 50px;
        }
        .social-icons {
            display: block;
            height: 48px;
        }
        .social-icons li {
            display: inline-block;
            margin-left: 6px;
        }
        @media screen and (min-width: 802px) {
            /* 800+ resolution */
            .controller {
                width: 942px;
                margin: auto;
            }
            .objects {
                width: 100%;
                height: 673px;
            }
            .text-area {
                width: 600px;
                padding-top: 20%;
                margin-left: 40px;
                text-align: center;
            }
            .error {
                font: 86px 'Nunito', sans-serif
            }
            .details, .homepage {
                font: 42px 'Nunito', sans-serif
            }
            .homepage {
                padding-left: 50px;
                float: right;
                right: 45px;
                top: -50px;
                position: relative;
                text-align: center;
            }
        }
        .copyrights {
            text-indent: -9999px;
            height: 0;
            line-height: 0;
            font-size: 0;
            overflow: hidden;
        }
        @media screen and (min-width: 602px) and (max-width: 801px) {
            /* 800- resolution */
            .controller {
                width: 800px;
                margin: auto;
            }
            .objects {
                width: 100%;
                height: 673px;
            }
            .text-area {
                width: 400px;
                padding-top: 20%;
                margin-left: 40px;
                text-align: center;
            }
            .error {
                font: 86px Jenna Sue, Helvetica, Arial, sans-serif;
            }
            .details, .homepage {
                font: 42px Jenna Sue, Helvetica, Arial, sans-serif;
            }
            .homepage {;
                padding-left: 50px;
                float: right;
                right: 25px;
                top: -50px;
                position: relative;
                text-align: center;
            }
        }
        @media screen and (min-width: 0px) and (max-width: 601px) {
            .controller {
                width: 600px;
                margin: auto;
            }
            .objects {
                width: 100%;
                height: 673px;
            }
            .text-area {
                width: 250px;
                padding-top: 20%;
                margin-left: 20px;
                text-align: center;
            }
            .error {
                font: 56px Jenna Sue, Helvetica, Arial, sans-serif;
            }
            .details, .homepage {
                font: 32px Jenna Sue, Helvetica, Arial, sans-serif;
            }
            .homepage {;
                padding-left: 35px;
                float: right;
                right: 20px;
                top: -50px;
                position: relative;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="controller">
    <div class="objects">
        <!-- text area -->
        <div class="text-area rotate">
            <p class="error">{{ $error }}</p>
            <p class="details"><br />{!! $message !!}</p>
        </div>
        <div class="homepage rotate">
            <a href="{{ admin_base_path('/') }}">Back to homepage</a>
        </div>
    </div>
</div>

</body>
</html>