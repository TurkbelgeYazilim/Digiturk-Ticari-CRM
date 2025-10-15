<!DOCTYPE html>

<html lang="tr">

<head>

	<meta charset="utf-8">

	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

	<title>ilekaSoft CRM - Giriş Ekranı</title>



	<!-- Favicon -->

	<link rel="shortcut icon" href="assets/img/favicon.png">



	<link rel="stylesheet" href="assets/css/bootstrap.min.css">



	<!-- Fontawesome CSS -->

	<link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">

	<link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">



	<!-- Main CSS -->

	<link rel="stylesheet" href="assets/css/style.css">



	<script src="https://lipis.github.io/bootstrap-sweetalert/dist/sweetalert.js"></script>

	<link rel="stylesheet" href="https://lipis.github.io/bootstrap-sweetalert/dist/sweetalert.css" />



	<!--[if lt IE 9]>

	<script src="assets/js/html5shiv.min.js"></script>

	<script src="assets/js/respond.min.js"></script>

	<![endif]-->

	<style>

		/* * * * * General CSS * * * * */

		*,

		*::before,

		*::after {

			box-sizing: border-box;

		}

		.toggle-password {

			top:59%;

		}

		.wrapper {

			margin: 0 auto;

			width: 100%;

			max-width: 1140px;

			min-height: 100vh;

			display: flex;

			align-items: center;

			justify-content: center;

			flex-direction: column;

		}



		.container {

			position: relative;

			width: 100%;

			max-width: 600px;

			height: auto;

			display: flex;

			background: #ffffff;

			box-shadow: 0 0 5px #999999;

			padding: 0;

		}



		.col-left,

		.col-right {

			padding: 30px;

			display: flex;

		}



		.col-left {

			width: 60%;

			-webkit-clip-path: polygon(0 0, 0% 100%, 100% 0);

			clip-path: polygon(0 0, 0% 100%, 100% 0);

			background: linear-gradient(135deg, #5c2d91 0%, #7c4dff 100%);

			margin: 0;

		}



		.col-right {

			padding: 30px 15px;

			width: 50%;

			margin-left: -10%;

		}



		@media(max-width: 575.98px) {

			.container {

				flex-direction: column;

				box-shadow: none;

			}



			.col-left,

			.col-right {

				width: 100%;

				margin: 0;

				-webkit-clip-path: none;

				clip-path: none;

			}



			.col-right {

				padding: 30px;

			}

		}



		.login-text {

			position: relative;

			width: 100%;

			color: #fff;

		}



		.login-text h2 {

			margin: 0 0 15px 0;

			font-size: 30px;

			font-weight: 700;

			color:#fff;

		}



		.login-text p {

			margin: 0 0 20px 0;

			font-size: 16px;

			font-weight: 500;

			line-height: 22px;

		}



		.login-text .btn {

			display: inline-block;

			padding: 7px 20px;

			font-size: 16px;

			letter-spacing: 1px;

			text-decoration: none;

			border-radius: 30px;

			color: #5c2d91;

			background: #fff;

			border: 1px solid #5c2d91;

			font-weight: bold;

			transition: .3s;

			-webkit-transition: .3s;

		}



		.login-text .btn:hover {

			color: #fff;

			background: #5c2d91;

			box-shadow: 0 0 10px #7c4dff;

		}



		.login-form {

			position: relative;

			width: 100%;

		}



		.login-form h2 {

			margin: 0 0 15px 0;

			font-size: 22px;

			font-weight: 700;

		}



		.login-form p {

			margin: 0 0 10px 0;

			text-align: left;

			color: #666666;

			font-size: 15px;

		}



		.login-form p:last-child {

			margin: 0;

			padding-top: 3px;

		}



		.login-form p a {

			color: #5c2d91;

			font-size: 14px;

			text-decoration: none;

		}



		.login-form label {

			display: block;

			width: 100%;

			margin-bottom: 2px;

			letter-spacing: .5px;

		}



		.login-form p:last-child label {

			width: 60%;

			float: left;

		}



		.login-form label span {

			color: #ff574e;

			padding-left: 2px;

		}



		.login-form input {

			display: block;

			width: 100%;

			height: 35px;

			padding: 0 10px;

			outline: none;

			border: 1px solid #cccccc;

			border-radius: 30px;

		}



		.login-form input:focus {

			border-color: #5c2d91;

			box-shadow: 0 0 0 2px #e0d7f7;

		}



		.login-form button,

		.login-form input[type=submit] {

			display: inline-block;

			width: 100%;

			margin-top: 5px;

			color: #fff;

			background: #5c2d91;

			border: 1px solid #5c2d91;

			font-weight: bold;

			transition: .3s;

			-webkit-transition: .3s;

		}



		.login-form button:hover,

		.login-form input[type=submit]:hover {

			color: #5c2d91;

			background: #fff;

			box-shadow: 0 0 10px #7c4dff;

		}



		.background-image {

			position: fixed;

			left: 0;

			right: 0;



			display: block;

			background-image: url("assets/img/BG-IMAGE.png");

			background-size:cover ;

			width: 100%;

			height: 100vh;

		}



	</style>

</head>

<body >

<div class="background-image">







</div>

<!-- Main Wrapper -->



<div class="main-wrapper login-body">





	<div class="wrapper">

		<div class="container">

			<div class="col-left">

				<div class="login-text">

					<h2></h2>

				</div>

			</div>

			<div class="col-right">

				<div class="login-form">



					<img class="img-fluid logo-dark " src="assets/img/logo2.png" alt="Logo" style="margin-bottom: 25px;">

					<form action="https://crm.ilekasoft.com/check/login" method="POST" >

						<p>

							<label class="form-control-label">e-Posta Adresi</label>

							<input type="email" name="u_email" class="form-control">

						</p>

						<p>

							<label>Şifre</label>

							<input type="password" name="u_password" class="form-control pass-input">

							<span class="fas fa-eye toggle-password"></span>

						</p>

						<p>

							<input type="submit" value="Giriş Yap!" />

						</p>



					</form>

	<br>

					<center><a href="https://ilekasoft.com/" style="color:#000;"><u>ilekaSoft.com</u></a></center>

				</div>

			</div>



		</div>

	</div>

</div>

<!-- /Main Wrapper -->





<?php if ($this->session->flashdata('oturum_acik')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "error",

			text: "Sisteme aynı anda birden fazla giriş yapamazsınız.",

			confirmButtonText:'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>





<?php if ($this->session->flashdata('login_fail')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "error",

			text: "Giriş bilgileriniz kayıtlarımız ile eşleşmedi, lütfen tekrar deneyiniz.",

			confirmButtonText:'Tamam',

			button: false,

			timer: 5000,

		});

	</script>

<?php endif; ?>



<?php if ($this->session->flashdata('login_fail_inactive')): ?>

	<script>

		swal({

			title: "Bilgilendirme",

			type: "error",

			text: "Kullanıcı hesabınız pasif duruma getirilmiştir, bir hata olduğunu düşünüyorsanız lütfen sistem yöneticisi ile iletişime geçiniz.",

			confirmButtonText:'Tamam',

			button: false,

			timer: 7500,

		});

	</script>

<?php endif; ?>

<!-- jQuery -->

<script src="assets/js/jquery-3.5.1.min.js"></script>



<!-- Bootstrap Core JS -->

<script src="assets/js/popper.min.js"></script>

<script src="assets/js/bootstrap.min.js"></script>



<!-- Feather Icon JS -->

<script src="assets/js/feather.min.js"></script>



<!-- Custom JS -->

<script src="assets/js/script.js"></script>



</body>

</html>

