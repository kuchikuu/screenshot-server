<?php
$token = "put your token here or remove it and remove the token check on line 18";
$uploaddir = "./";

# https://www.php.net/manual/en/features.file-upload.errors.php
$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
);


if(isset($_FILES["image"])){
	if(isset($_POST["token"])){
		if($_POST["token"] != $token){
			die();
		};
	}else{
		die();
	};
	if(isset($_FILES["image"]["error"])){
		if($_FILES["image"]["error"] == 0){
			#$uploadfile = $uploaddir . "screenshot.jpg";
			$newfilename = md5_file($_FILES["image"]["tmp_name"]) . ".jpg";
			$uploadfile = $uploaddir . $newfilename;
			if(move_uploaded_file($_FILES["image"]["tmp_name"], $uploadfile)){
				echo "https://your_server_address" . $newfilename;	# Change this for your server
			};
		}else{
			print($phpFileUploadErrors[$_FILES["image"]["error"]]);
		};

	};
	#For debug:
	#print_r($_FILES);

}else{
	print("");
	/*
	print("Upload a file:<br>");
	print '
<form enctype="multipart/form-data" action="" method="POST">
    <input type="hidden" name="MAX_FILE_SIZE" value="2000000" />
    Send this file: <input name="image" type="file" />
    <input type="submit" value="Send File" />
</form>
';*/
};

?>
