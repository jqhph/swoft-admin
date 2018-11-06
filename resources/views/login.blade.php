<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{config('admin.title')}} | {{ t('Login', 'admin') }}</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  {!! html_css("@admin/AdminLTE/bootstrap/css/bootstrap.min.css") !!}
  <!-- Font Awesome -->
  {!! html_css("@admin/font-awesome/css/font-awesome.min.css") !!}
  <!-- Theme style -->
  {!! html_css("@admin/AdminLTE/dist/css/AdminLTE.min.css") !!}

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <style>
    .login-wrapper{
      box-shadow: 0 1px 5px rgba(0, 0, 0, .2), 0 2px 2px rgba(0, 0, 0, .14), 0 3px 1px -2px rgba(0, 0, 0, .12);
    }
    .login-page{
      background:#ebeff2;
      {{--background: url({{admin_asset('login/bg1.jpg')}}) repeat;background-size: cover;--}}
      /*background-position:320px;*/
    }
    .login-logo{
      font-family: 'Rancho',cursive,'Raleway', sans-serif;
      font-size:40px;
    }
    /*.login-logo a{*/
      /*color:#fff*/
    /*}*/
    .login-wrapper .login-box-body {
      padding:30px 25px;
    }
    .login-wrapper .login-title{
      height:65px;
      background: #1867c0!important;
      color:#fff;
      padding:20px;
      font-family: 'Raleway', sans-serif;
    }
    .login-wrapper .login-title .box-msg{
      font-size:18px;
      font-weight:500;
    }
    .btn-primary {
      background-color: #1867c0 !important;
      border-color: #1867c0 !important;
    }

    .btn-primary:hover, .btn-primary:focus, .btn-primary:active, .btn-primary.active, .btn-primary.focus, .btn-primary:active, .btn-primary:focus, .btn-primary:hover, .open > .dropdown-toggle.btn-primary {
      background-color: #1867c0 !important;
      opacity: 0.9;
    }
    .btn {
      will-change: box-shadow !important;
      box-shadow: 0px 3px 1px -2px rgba(0, 0, 0, 0.15), 0px 2px 2px 0px rgba(0, 0, 0, 0.15), 0px 1px 5px 0px rgba(0, 0, 0, 0.15)!important;
    }
    .login-label{
      font-weight:500;
      margin-bottom:8px;
    }
    .checkbox label, .radio label{
      padding-left:10px;
    }
    .checkbox input[type=checkbox]{
      margin-left:-10px;
    }
  </style>
</head>
<body class="hold-transition login-page" style="">
<div class="login-box ">
  <div class="login-logo">
    <a href="{{ admin_base_path() }}">{{config('admin.name')}}</a>
  </div>
  <div class="login-wrapper">
    {{--<div class="login-title" style="display: none">--}}
      {{--<span class="box-msg">{{ t('Login', 'admin') }}</span>--}}
    {{--</div>--}}
    <div class="login-box-body">
      <form action="{{ admin_base_path() }}" method="post">
        <div class="form-group has-feedback {!! !$errors->has('username') ?: 'has-error' !!}">

          @if($errors->has('username'))
            @foreach($errors->get('username') as $message)
              <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label></br>
            @endforeach
          @endif

          <label class="login-label">{{ t('Username', 'admin') }}</label>
          <input type="input" class="form-control" placeholder="{{ t('Username', 'admin') }}" name="username" value="{{ http_input('username') }}">
          <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        </div>
        <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

          @if($errors->has('password'))
            @foreach($errors->get('password') as $message)
              <label class="control-label" for="inputError"><i class="fa fa-times-circle-o"></i>{{$message}}</label></br>
            @endforeach
          @endif

          <label class="login-label">{{ t('Password', 'admin') }}</label>
          <input type="password" class="form-control" placeholder="{{ t('Password', 'admin') }}" name="password">
          <span class="glyphicon glyphicon-lock form-control-feedback"></span>
        </div>

        <div class="form-group ">
          <div class="col-xs-12">
            <div class="checkbox checkbox-custom">
              <input id="checkbox-signup" name="remember" type="checkbox">
              <label for="checkbox-signup">
                {{ t('Remember me', 'admin') }}
              </label>
            </div>

          </div>
        </div>

        <div class="row">

          <!-- /.col -->
          <div class="col-xs-7" style="top:8px">
            <input type="hidden" name="_token" value="{{ session()->token() }}">
            <a href="{{admin_base_path()}}" class="btn btn-primary btn-block btn-flat">
              点我不用登陆
            </a>
          </div>
          <!-- /.col -->
        </div>
      </form>

    </div>
  </div>
</div>

{!! html_js('@admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') !!}
<!-- Bootstrap 3.3.5 -->
{!! html_js('@admin/AdminLTE/bootstrap/js/bootstrap.min.js') !!}
<script>
  $(function () {

  });
</script>
</body>
</html>
