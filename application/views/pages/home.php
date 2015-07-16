<?php $this->load->view('widgets/header.php'); ?>
	<link rel="stylesheet" href="./static/css/home.css">
</head>
<body>

<?php $this->load->view('widgets/nav.php'); ?>

<div class="main">
	<div class="header-image">
		<div class="wrapper">
			<ul>
				<li><i></i><img src="./static/image/online.png"></li>
				<li><i></i><img src="./static/image/problem.png"></li>
				<li><i></i><img src="./static/image/study.png"></li>
				<li><i></i><img src="./static/image/god.png"></li>
				<li><i></i><img src="./static/image/Tree.png"></li>
			</ul>
		</div>
	</div>
	<div class="Carousel">
		<ul class="Carousel-image">
			<!-- li内的颜色为模拟图片展现-->
			<li style="background:#cc0000" class="Carousel-hover"></li>
			<li style="background:#FCC0FD"></li>
			<li style="background:#000"></li>
			<li style="background:#0036ff"></li>
			<li style="background:#F0F"></li>
		</ul>
		<ul class="Carousel-nav">
			<li class="hover"></li>
			<li></li>
			<li></li>
			<li></li>
			<li></li>
		</ul>
	</div>
	<div class="wrapper">

	</div>
</div>

<?php $this->load->view('widgets/footer.php'); ?>
<script src="./static/js/home.js"></script>

</body>
</html>
