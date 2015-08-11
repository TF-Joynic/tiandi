<?php $this->load->view('widgets/admin/header.php'); ?>
<link rel="stylesheet" href="static/css/admin/classListSite.css" type="text/css" />
</head>

<body>
<?php $this->load->view('widgets/admin/left.php' , array("activeNav" => 3)); ?>
	<script>
		id = "{type}";
	</script>
	<div class="main">
		<div class="main-content">
			<?php $this->load->view('widgets/admin/window.php'); ?>
			<div class="main-title">
				<ul class="nav nav-pills title">
					<?php $this->load->view('widgets/classList/min.nav.php' , array("activeNav" => 1)); ?>
					<li style="float:right;font-weight:700"><p>当前操作的课程详情页：{title}</p></li>
				</ul>
			</div>
			
			<div class="main-data">
					<div class="site-box">
						<h2><i class="fa fa-tag"></i>课程特色介绍</h2><br>
						<table class="table table-bordered">
							<tr>
								<th width="40%">名称</th>
								<th width="8%">地址</th>
								<th width="8%">操作</th>
							</tr>
							{tags}
							<tr data-id="{id}">
								<td>{name}</td>
								<td><a href="{link}">点击浏览</a></td>
								<td><i class="fa fa-edit edit-slider"></i><i class="fa fa-trash remove-tag"></i></td>
							</tr>
							{/tags}
						</table>
						<button class="btn btn-primary" style="float:right" id="add-classList"><i class="fa fa-tag"></i> &nbsp;&nbsp;&nbsp;添加课程特色标签</button>
					</div>
					
					
					<div class="site-box">
						<h2><i class="fa fa-external-link"></i>报名地址及描述</h2><br>
						<table>

							<tr>
								<td>报名按钮地址：</td>
								<td><input type="text" class="link" value="<?=$steps['link']?>" placeholder="请在此处填写报名地址"></td>
							</tr>
							<tr>
								<td>课程详情描述：</td>
								<td><textarea  name="direction" class="direction" placeholder="请在此处填写课程详情描述">{description}</textarea></td>
								<td><button class="btn btn-primary" style="float:right" id="save-link" data-id="{id}"><i class="fa fa-save"></i> &nbsp;保存</button></td>
							</tr>
						</table>
					</div>
					
					
					<div class="site-box">
						<h2>公开课课程发布时间设置</h2><br>
						<table class="table table-bordered">
							<tr>
								<th width="40%">名称</th>
								<th>发布时间</th>
								<th>发布内容</th>
								<th width="8%">操作</th>
							</tr>
							{course_0}
							<tr data-id="{id}">
								<td>{title}</td>
								<td>{time}</td>
								<td>{content}</td>
								<td><i class="fa fa-edit edit-public" data-type="0"></i><i class="fa fa-trash remove-public"></i></td>
							</tr>
							{/course_0}
						</table>
						<button class="btn btn-primary public-class" style="float:right" data-type="0"><i class="fa fa-list"></i> &nbsp;添加课程</button>
					</div>
					
					
					<div class="site-box">
						<h2>付费课开课时间设置</h2><br>
						<table class="table table-bordered">
							<tr>
								<th width="40%">名称</th>
								<th>时间</th>
								<th>内容</th>
								<th width="8%">操作</th>
							</tr>
							{course_1}
							<tr data-id="{id}">
								<td>{title}</td>
								<td>{time}</td>
								<td>{content}</td>
								<td><i class="fa fa-edit edit-public" data-type="1"></i><i class="fa fa-trash remove-public"></i></td>
							</tr>
							{/course_1}
						</table>
						<button class="btn btn-primary public-class" style="float:right" data-type="1"><i class="fa fa-list"></i> &nbsp;添加课程</button>
					</div>
					
					
					
					
					<div class="site-box">
						<h2>课程内容章节设置</h2><br>
						<table class="table table-bordered">
							<tr>
								<th width="40%">名称</th>
								<th>内容</th>
								<th width="8%">操作</th>
							</tr>
							{data_chapter}
							<tr data-id="{id}">
								<td>{title}</td>
								<td>{content}</td>
								<td><i class="fa fa-edit edit-classContent"></i><i class="fa fa-trash remove-classContent"></i></td>
							</tr>
							{/data_chapter}
						</table>
						<button class="btn btn-primary" style="float:right" id="add-classContent"><i class="fa fa-list"></i> &nbsp;添加课程</button>
					</div> 			


					<div class="site-box updata">
						
							<h2>设置课程照片</h2><br>
							<div class="updataBox">
								<img src="<?=!isset($steps['img']) ? "static/image/slide1.jpg" : './static/uploads/' . $$steps['img'] ?>" class="preview">
								<form enctype="multipart/form-data" action="api/upload/pic" id="classpic" method="post">
									<input type="hidden" name="type" value="{type}" class="updata-class">
									<input type="file" name="userfile" class="updata-class">
								</form>
							</div>
							<button class="btn btn-success" style="float:right" id="updataPic"><i class="fa fa-list"></i> &nbsp;保存</button>
					</div> 
					
					
					
			</div>
			

		</div>
	</div>
	<?php $this->load->view('widgets/admin/footer.php'); ?>
	<link rel="stylesheet" href="./static/css/datepicker.css">
	  <script src="./static/lib/bootstrap-datepicker.js"></script>
	<script src="./static/js/admin/classListSite.js"></script>

</body>
</html>