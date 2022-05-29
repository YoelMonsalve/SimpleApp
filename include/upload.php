<?php

/**
 * This class is used to upload files from the local computer to
 * the remote host.
 *
 * Author   : Yoel Monsalve.
 * Date     : 2022-05-29
 * Version  : ---
 * Modified : 2022-05-29
 */

class Uploader {

  private $fileName;
  private $fileType;
  private $fileTempPath;
  private $fileDestPath;

  public $errors = array();
  /**
   * Errors translation table. Each code is automatically given by the inner
   * PHP upload method.
   */
  public $upload_errors = array(
    0 => 'Success (no error)',
    1 => 'File exceeds the `UPLOAD_MAX_FILESIZE` directive in php.ini',
    2 => 'File exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'File was only partially uploaded',
    4 => 'No file was uploaded',
    5 => 'Destination path for the file is undefined',
    6 => 'Missing a temporary folder',
    7 => 'Failed when writing file to disk.',
    8 => 'A PHP extension stopped the file upload.'
  );

  /* yoel: Changed this member to *PRIVATE*, this way NOT allowing to
   *       upload other types of files (security breach) 
   *
   * 2021.02.24.-
   */
  private $upload_extensions = array(
    'gif',
    'jpg',
    'jpeg',
    'png',
    'json',
    'txt',
    'csv'
  );
  
	/* === added by Yoel.- 2019.03.11 === 
	 * Constructor
	 */
	public function __construct() 
  { 
    $this->fileName = "";
    $this->fileType = "";
    $this->fileTempPath = "";
    $this->fileDestPath = "";
	}
  public function __destruct() 
  {
    $this->fileTempPath = "";
    unset($this->fileTempPath);
  }
 
 	/**
   * Properly set paths for file being uploaded
   */
	private function set_paths($file) 
  {
    $this->fileName     = basename($file['name']);
    $this->fileType     = $file['type'];
    $this->fileTempPath = $file['tmp_name'];           // path assigned by the <input type="file"> control
    $this->fileDestPath = SITE_ROOT . DS . 'data/';

    echo "fileName: ".$this->fileName . "<br ?>";
    echo "fileType: ".$this->fileType . "<br ?>";
    echo "fileTempPath: ".$this->fileTempPath . "<br ?>";
    echo "fileDestPath: ".$this->fileDestPath . "<br ?>"; 
	}
	
  /* yoel: name name changed to "is_correct_file_type"
   * 2021.02.24.- */
  public function is_correct_file_type($filename) 
  {
    return in_array($this->file_ext($filename), $this->upload_extensions);
  }

  /**
   * File extension
   */
  private function file_ext($filename) 
  {
    return pathinfo($filename, PATHINFO_EXTENSION);
  }

  /**
   * Check if the file is suitable to be correctly uploaded.
   *
   * @return  bool  
   */
  public function check_file() {
    if (!empty($this->errors)) {
      return false;
    }
    elseif (empty($this->fileName) || empty($this->fileTempPath) || empty($this->fileDestPath)) {
      $this->errors[] = "File path is unknown.";
      return false;
    }
    elseif (!is_writable($this->fileDestPath)) {
      $this->errors[] = "No write permissions over the destination folder";
      return false;
    }
    /*elseif (file_exists($this->fileDestPath.DS.$this->fileName)) {
      $this->errors[] = "The file {$this->fileName} already exists";
      return false;
    }*/
    else {
      return true;
    }
  }

  /**
   * Upload the file
   */
  public function upload($file)
  {
    /* pre-verify the `$file` variable, set by the <input type="file"> */
    echo "upload 1";
    if( !$file || empty($file) || !is_array($file) ) {
      $this->errors[] = "There is no file to be uploaded";
      return false;
    }
    echo "upload 2";
    if( $file['error'] != 0 ) {
      /* error per PHP upload method */ 
      $this->errors[] = $this->upload_errors[$file['error']];
      return false;
    }
    echo "upload 3";
    if( !$this->is_correct_file_type($file['name']) ) {
      $this->errors[] = "Incorrect file type";
      return false;
    }

    echo "upload 4";
    /* if correct, then set paths and check them */
    $this->set_paths($file);
    echo "upload 5";
    if (!$this->check_file()) {
      print_r($this->errors);
      return false;
    }
    echo "upload 6";
    /* and move to the destination folder */
    echo $this->fileTempPath . "<br>";
    echo $this->fileDestPath.$this->fileName . "<br>";
    //die();
    if (move_uploaded_file($this->fileTempPath, $this->fileDestPath.$this->fileName)) {
      $this->fileTempPath = "";
      unset($this->fileTempPath);
      echo "Upload 7 .. Success";
      return true;
    } 
    else {
      $this->errors[] = "An error occurred during file upload. Check folder permissions, and file type.";
      return false;
    }
  }
}
?>