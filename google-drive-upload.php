<?php
require_once "google/google-api-php-client/src/Google_Client.php";
require_once "google/google-api-php-client/src/contrib/Google_DriveService.php";
require_once "google/google-api-php-client/src/contrib/Google_Oauth2Service.php";
require_once "google/vendor/autoload.php";

$DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive';
$SERVICE_ACCOUNT_EMAIL = 'your email @iam.gserviceaccount.com';
$SERVICE_ACCOUNT_PKCS12_FILE_PATH = 'directory to file .p12';

function buildService() {//function for first build up service
    global $DRIVE_SCOPE, $SERVICE_ACCOUNT_EMAIL, $SERVICE_ACCOUNT_PKCS12_FILE_PATH;
    $key = file_get_contents($SERVICE_ACCOUNT_PKCS12_FILE_PATH);
    $auth = new Google_AssertionCredentials(
        $SERVICE_ACCOUNT_EMAIL,
        array($DRIVE_SCOPE),
        $key);
    $client = new Google_Client();
    $client->setUseObjects(true);
    $client->setAssertionCredentials($auth);
    return new Google_DriveService($client);
}

function insertFile($service, $title, $description, $parentId, $filename) {//function for insert a file

    $file = new Google_DriveFile();
    $file->setTitle($title);
    $file->setDescription($description);
    /* $file->setMimeType($mimeType);*/

    // Set the parent folder.
    if ($parentId != null) {
        $parent = new Google_ParentReference();
        $parent->setId($parentId);
        $file->setParents(array($parent));
    }

    try {
        //set the file with MIME
        $permission = new Google_Permission();
        $permission->setRole( 'writer' );
        $permission->setType( 'anyone' );
        $permission->setValue( 'me' );
        $service->permissions->insert( $createdFile->getId(), $permission );

        //insert permission for the file



        return $createdFile;
    } catch (Exception $e) {
        print "An error occurred1: " . $e->getMessage();
    }
}


try {
    $root_id='yourrootid';
    $service=buildService();
    $mimes = array(
        'image/jpeg', 'image/png', 'image/gif'
    );
    sleep(2);
    if (isset($_FILES['myfile'])) {
        $fileName = $_FILES['myfile']['name'];
        $fileType = $_FILES['myfile']['type'];
        $fileError = $_FILES['myfile']['error'];
        $fileStatus = array(
            'status' => 0,
            'message' => '' 
        );
        if ($fileError== 1) { 
            $fileStatus['message'] = 'File is too large, try to split into multi-part archives';
        } elseif (!in_array($fileType, $mimes)) {
            $fileStatus['message'] = 'File type is not allowed';
        } else {
            move_uploaded_file($_FILES['myfile']['tmp_name'], 'vtemp/'.$fileName);
            $file_destination = 'vtemp/'.$fileName;
            $fileStatus['status'] = 1;
            $fileStatus['message'] = "$fileName uploaded succesfully";
            $title=$fileName;
            $description='';
            $parentId=$root_id;
            $parentId=insertFile($service, $title, $description, $parentId, $file_destination);
        }   
        echo json_encode($fileStatus);
        exit();
    }
}

catch (Exception $e) {
    print "An error occurred1: " . $e->getMessage();
}

?>

