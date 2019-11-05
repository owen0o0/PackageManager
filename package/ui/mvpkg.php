<?php

#Regular Expression Online tester: https://regex101.com/
#PHP functions Online tester: https://fr.functions-online.com/preg_match.html
#Help HTML 5: https://www.scriptol.fr/html5/

ob_start();

$PACKAGE = $_POST['package'];
$TARGET = $_POST['volume'];
if ($PACKAGE != '' && $TARGET != '') {
	switch ($TARGET) {
		case "start":
			$action = "sudo /usr/syno/sbin/synoservicecfg --hard-start 'pkgctl-$PACKAGE' 2>&1";
			break;
		case "stop":
			$action = "sudo /usr/syno/sbin/synoservicecfg --hard-stop 'pkgctl-$PACKAGE' 2>&1";
			break;
		case "reverse":
			$action = "sudo /usr/syno/sbin/synoservicecfg --reverse-dependency 'pkgctl-$PACKAGE' 2>&1";
			$dep = "sudo /usr/syno/sbin/synoservicecfg --reverse-dependency 'pkgctl-$PACKAGE'";
			break;
		case "forward":
			$action = "sudo /usr/syno/sbin/synoservicecfg --forward-dependency 'pkgctl-$PACKAGE' 2>&1";
			$dep = "sudo /usr/syno/sbin/synoservicecfg --forward-dependency 'pkgctl-$PACKAGE'";
			break;
		case "erase":
			$action = "sudo ".dirname(__FILE__)."/rmpkg.sh '$PACKAGE' 2>&1";
			$TARGET = "";
			$PACKAGE = "";
			break;
		default:
			$action = "sudo ".dirname(__FILE__)."/mvpkg.sh '$TARGET' '$PACKAGE' 2>&1";
			$TARGET = "";
			$PACKAGE = "";
	}
}

if ($action != '') {
	//ob_start();
	//passthru($move);
	//$output = ob_get_clean();
	exec($action, $output, $result);
}
?>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<link rel="icon" type="image/png" href="favicon.png">
</head>
<body style="display:none">
<script type="text/javascript" src="spin.min.js"></script>
<script>
	$(document).ready(function(){
		$("#volume").prop("disabled", true);
		$('body').show();
				
		var selectPackage = function($name) {
			var package = $name;
			
			//volume = $("#packages option[value='" + package + "']").attr('data-volume');
			volume = $("#packages option[value='" + package + "']").data('volume');
			//alert('Select ' + package + ' on ' + volume);

			$("#volume").val('');

			if (volume != null) {
				$("#currentVolume").text("目前在 " + volume);
			} else {
				$("#currentVolume").text('');
			}
			
			$("#move").prop("disabled", true);
			
			var id = $('#packages option').filter(function() {
				return this.value == package;
			}).val();

			var elem = $("#" + package);
			var elemState = elem.css('color');
			//alert(elemState);
			
			if (package == id) {
				$("#volume").prop("disabled", false);
				if ( elemState == 'rgb(0, 0, 255)') {
					$("#start").prop("disabled", true);
					$("#stop").prop("disabled", false);
				} else {
					$("#start").prop("disabled", false);
					$("#stop").prop("disabled", true);					
				}
				$("#reverse").prop("disabled", false);
				$("#forward").prop("disabled", false);
				$("#erase").prop("disabled", false);
			} else {
				$("#volume").prop("disabled", true);
				$("#start").prop("disabled", true);
				$("#stop").prop("disabled", true);
				$("#reverse").prop("disabled", true);
				$("#forward").prop("disabled", true);
				$("#erase").prop("disabled", true);				
			}
			
			hideVolume(volume);
		}
		
		var preload = $("#package").val();
		if (preload != '') {
			selectPackage(preload);
		}
		
		$("#package").on('input', function(){
			var package = $(this).val();
			var volume = $(this).text(); //Has no value ?!?!
			//alert('Select ' + package + ' on ' + volume);

			selectPackage(package);
		});	

		$("#volume").on('input', function(){
			var package = $(this).val();
			var id = $('#volumes option').filter(function() {
				return this.value == package;
			}).data("volume");
			if (package == id) {
				$("#move").prop("disabled", false);
			} else {
				$("#move").prop("disabled", true);
			}
		});
		
		$( "#reset" ).click(function() {
			$("#package").val('');
			$("#volume").val('');
			$("#currentVolume").text('');
			$("#move").prop("disabled", true);
			$("#start").prop("disabled", true);
			$("#stop").prop("disabled", true);
			$("#reverse").prop("disabled", true);
			$("#forward").prop("disabled", true);
			$("#erase").prop("disabled", true);			
		});

		$( "#refresh" ).click(function() {
			$("#reset").click();
			$("#loadingspinner").text("重新加载套件...");
			waitAndSubmit();
		});

		$( "#start" ).click(function() {
			$("#volume").val('start');
			$("#loadingspinner").text("启动套件...");
			waitAndSubmit();
		});

		$( "#stop" ).click(function() {
			$("#volume").val('stop');
			$("#loadingspinner").text("停用套件...");
			waitAndSubmit();
		});
		
		$( "#forward" ).click(function() {
			$("#volume").val('forward');
			$("#loadingspinner").text("查询套件...");
			waitAndSubmit();
		});

		$( "#reverse" ).click(function() {
			$("#volume").val('reverse');
			$("#loadingspinner").text("查询套件...");
			waitAndSubmit();
		});		

		$( "#erase" ).click(function() {
			if (confirm("您确定删除此软件包吗？这可能无法复原！")) {
			$("#volume").val('erase');
			$("#loadingspinner").text("擦除套件...");
			waitAndSubmit();
			}
		});
		
		$(".package").click(function() {
			var package = $(this).attr('id');
			$("#package").val(package);
			
			selectPackage(package);
		});
		
		$("#move").click(function () {
			$("#loadingspinner").text("移动套件...");
			waitAndSubmit();
		});		
	});

	function waitAndSubmit() {
		$("#loading").fadeIn();
		var opts = {
			lines: 12, // The number of lines to draw
			length: 7, // The length of each line
			width: 4, // The line thickness
			radius: 10, // The radius of the inner circle
			color: '#000', // #rgb or #rrggbb
			speed: 1, // Rounds per second
			trail: 60, // Afterglow percentage
			shadow: false, // Whether to render a shadow
			hwaccel: false // Whether to use hardware acceleration
		};
		var target = document.getElementById('loading');
		var spinner = new Spinner(opts).spin(target);
		$( "form" ).submit();
	}
	
	function hideVolume(volume) {
		$("#volumes option").each(function() {
			var id = $(this).val();
			var name = $(this).text();
			//alert('Found val=' + id + ' - text=' + id);
			
			if (id == volume) {
				//alert(volume + ' must be hidden');
				$(this).prop("disabled", true);
			} else {
				$(this).prop("disabled", false);
			}
		});		
	}
	
	function unhighlight(x) {
	  x.style.backgroundColor = "#cfe8ff"
	}

	function highlight(x) {
	  x.style.backgroundColor = "#94adff"
	}
</script>
<style>
	fieldset
	{
		margin-top:12px;
		border: 2px solid #069;
		padding:12px;
		-moz-border-radius:8px;
		border-radius:8px;
		background-color: #F5FAFF;
	}
	fieldset legend
	{
		color:#069;
		font-weight: 700;
	}
	fieldset table tr {
		height: 30px;
	}
	fieldset table tr td span
	{
		padding: 2px 8px;
    	border-radius: 5px;
		cursor:pointer;
		background: rgb(207, 232, 255);
	}
	input[type="button"],input[type="submit"],input[type="submit"]:disabled 
	{
		padding: 5px 8px;
    	border-radius: 5px;
    	box-shadow: none;
		cursor:pointer;
    	border: 1px solid #068;
	}
	input[type="button"]:disabled ,input[type="submit"]:disabled 
	{
		cursor:no-drop;
    	border: 1px solid #aaa;
	}
	input[type="button"]:hover,input[type="submit"]:hover
	{
		background:#fff
	}
	input[type="text"]
	{
		padding: 8px 15px;
    	margin-right: 10px;
    	border-radius: 5px;
    	border: 1px solid #aaa;
	}
    #loading
    {
        display:none;
        position:fixed;
        left:0;
        top:0;
        width:100%;
        height:100%;
        background:rgba(255,255,255,0.8);
        z-index:1000;
    }
  
    #loadingcontent
    {
        display:table;
        position:fixed;
        left:0;
        top:0;
        width:100%;
        height:100%;
    }
  
    #loadingspinner
    {
        display: table-cell;
        vertical-align:middle;
        width: 100%;
        text-align: center;
        font-size:larger;
        padding-top:80px;
    }
</style>

<?php

#Get Packages information
$packages = shell_exec('ls -la /var/packages/*/target');
foreach(preg_split("/((\r?\n)|(\r\n?))/", $packages) as $package){
	$out = null;
	if ( preg_match('/(\\/var\\/packages\\/([^\\/]*)\\/target) -> (\\/(volume\\d+)\\/@appstore\\/\\2)/', $package, $out) ) {
		$link = $out[1];
		$name = $out[2];
		$path = $out[3];
		$volume = $out[4];	
		$arr[$name] = [$volume, $path, $link];
		
		$usedVolumes[$volume] = $volume;
	}
}

if ($dep != '') {
	$dependencies = shell_exec($dep);
	//Test: $dependencies = "service [pkgctl-WebStation] is depended by service [pkgctl-Apache2.4] [pkgctl-WordPress] [pkgctl-Apache2.2] [pkgctl-phpMyAdmin]  (list by disable order)";
	foreach(explode(" ", $dependencies) as $dependency){
		$out = null;
		if ( preg_match('/\[pkgctl-(.*)\]/', $dependency, $out) ) {
			$elem = $out[1];	
			$dependent[$elem] = 1;
		}
	}
}

#Get list of volumes
$volumeNames = shell_exec('df -la --output=target | grep volume');

foreach(preg_split("/((\r?\n)|(\r\n?))/", $volumeNames) as $volumeName){
	$out = null;
	if ( preg_match('/volume\\d+/', $volumeName, $out) ) {
		$volume = $out[0];
		$volumes[$volume] = $volume;
	}
}

#Get Packages status
$statusList = shell_exec('sudo /usr/syno/sbin/synoservicecfg --status');

//Test: $statusList = "
// Service [pkgctl-Init_3rdparty] status=[enable]
// required upstart job:
        // [pkgctl-Init_3rdparty] is start.
// =======================================
// Service [pkgctl-PkgMover] status=[enable]
// required upstart job:
        // [pkgctl-PkgMover] is start.
// =======================================
// Service [pkgctl-filebot-node] status=[enable]
// required upstart job:
        // [pkgctl-filebot-node] is start.
// =======================================
// Service [pkgctl-NoteStation] status=[enable]
// required upstart job:
        // [pkgctl-NoteStation] is start.
// =======================================";

foreach(preg_split("/((\r?\n)|(\r\n?))/", $statusList) as $status){
	$out = null;
	if ( preg_match('/\[pkgctl-(.*)\] status=\[(.*)\]/', $status, $out) ) {
		$service = $out[1];
		$serviceStatus = $out[2];
		$PackageStatus[$service] = $serviceStatus;
	}
}

uksort($usedVolumes, "strnatcasecmp");
uksort($volumes, "strnatcasecmp");
uksort($arr, "strnatcasecmp");

foreach ($usedVolumes as $key => $volume){
	echo "<fieldset><legend>在 $volume 中的套件</legend>";
	$col = 1;
	echo "<table><tr>";
	foreach ($arr as $package => $data){
		if ($data[0] == $volume) {
			//$cmd = "/usr/syno/sbin/synoservicectl --status 'pkgctl-$package'";
			//$state = shell_exec($cmd);
							
			echo "<td width=200><span id='$package' onmouseover='highlight(this);' onmouseout='unhighlight(this)' class='package'";
			if ($PackageStatus[$package] == "enable") {
				if ($dependent[$package] == 1)
					echo " style='color:black;text-decoration:underline'>";
				else
					echo " style='color:black'>";
			} else {
				if ($dependent[$package] == 1)
					echo " style='color:rgb(189,189,189);text-decoration:underline'>";				
				else
					echo " style='color:rgb(189,189,189)'>";
			}
			echo $package;
			echo "</span></td>";
			$col = $col +1;
			if ($col % 5 == 0) {
				$col = 1;
				echo "</tr>\n<tr>";
			}
		}
	}
	echo "</tr></table>";
	echo "<div style='color:grey;font-size:9px' align='right'>灰显的套件表示被禁用</div>";
	echo "</fieldset>";
}

echo "<br/><input type='button' id='refresh' style='float: right;' value='刷新'><br/><br/>\n";
//echo "<hr>\n";

echo "<form action='mvpkg.php' method='post'>";
echo "<fieldset><legend>选择要移动的套件</legend>\n";
echo "<p><input type='text' list='packages' id='package' name='package' value='$PACKAGE'><span id='currentVolume'></span>";
echo "<datalist id='packages' >";
foreach ($arr as $package => $data){
	echo "<option data-volume='$data[0]' value='$package'>$data[0]\n";
}
echo "</datalist>\n";
echo "</p>\n";
echo "</fieldset>\n";


echo "<fieldset><legend>目标存储空间</legend>";
echo "<p><input type='text' list='volumes' id='volume' name='volume'>";
echo "<datalist id='volumes' >";
foreach ($volumes as $key => $volume){
	echo "<option data-volume='$key' value='$key'>$key";
}
echo "</datalist>";
echo "</p>";
echo "</fieldset>";
echo "<br/><br/>";
echo "<input type='button' id='reset' value='重置' title='清除当前选择'> ";
echo "<input type='submit' id='move' value='移动套件' disabled> ";
echo "<input type='button' id='start' value='启动' title='启动所选服务' disabled> \n";
echo "<input type='button' id='stop' value='停用' title='停用所选服务' disabled> \n";
echo "<input type='button' id='forward' value='Forward Dep' title='显示选定服务所依赖的所有服务。' disabled> \n";
echo "<input type='button' id='reverse' value='Reverse Dep' title='显示所有服务，具体取决于所选的一项。' disabled> \n";
echo "<input type='button' id='erase' value='擦除' style='float: right;' title='删除程序包，并忽略相关性（如果有）。' disabled> \n";
//echo "<hr> \n";
if ($action != '') {

	echo "<fieldset><legend>信息</legend>";
	foreach ($output as $item => $data){
		echo "<p>$data</p>";	
	}
	echo "</fieldset>";	
}
ob_end_flush();
?>
<div id="loading">
    <div id="loadingcontent">
        <p id="loadingspinner">
			移动套件...
        </p>
    </div>
</div>
</body>
</html>