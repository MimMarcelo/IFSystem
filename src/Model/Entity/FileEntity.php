<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Upload
 * Define a type able to upload a file to server
 * 
 * @property string $tmpName Temporary file's name
 * @property string $fileName Currently file's name
 * @property string $fileExtension File's extension
 * @property string $pathToSaveFile Local to save file
 * @property float $fileSize File's size in MB
 * @property array() $error List of error messages
 */
class FileEntity{

    /**
     * Build a FileUpload object
     * 
     * @param type $_file Your $_FILES['file'] to be uploaded
     */
    public function __construct($_file) {
        $this->tmpName = $_file["tmp_name"];
        $this->fileName = $_file['name'];
        $this->fileExtension = strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
        $this->pathToSaveFile = "";
        $this->fileSize = $_file["size"];
        $this->error = array();
    }

    /**
     * Returns an array with all error messages received
     * 
     * @return array()
     */
    public function getError() {
        return $this->error;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getExtension() {
        return $this->fileExtension;
    }
    /**
     * Enable to change the file name to be saved
     * 
     * @param string $fileName
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    /**
     * Enable to set where the file may be saved
     * <p>
     * Make sure to put a slash "/" at the end of path
     * </p>
     * 
     * @param string $pathToSaveFile
     */
    public function setPathToSaveFile($pathToSaveFile) {
        $this->pathToSaveFile = WWW_ROOT . DS . $pathToSaveFile . DS;
    }

    /**
     * Enable test some features about the file like:
     * 
     * @param boolean $allowOverride       Enable override file
     * @param float $maxSizeInMb         Max file size enabled (in MB)
     * @param array() $extensionsEnabled   Extensions enabled to upload
     * @return boolean true if all validations were appoved
     */
    public function validate($allowOverride = true, $maxSizeInMb = 0, $extensionsEnabled = null) {
        if (!$allowOverride) {
            if (file_exists($this->pathToSaveFile . $this->fileName)) {
                $this->error[] = "File name '$this->fileName' already exists";
                return false;
            }
        }
        if ($maxSizeInMb > 0) {
            if (!$this->fileSizeLessThan($maxSizeInMb)) {
                $this->error[] = "File size greater than limit of " . $maxSizeInMb . "MB";
                return false;
            }
        }
        if ($extensionsEnabled) {
            if (!$this->checkExtension($extensionsEnabled)) {
                $this->error[] = "File extension not enabled";
                return false;
            }
        }
        return true;
    }

    /**
     * Move the file to server directory
     * 
     * @return boolean true if success
     */
    public function upload() {
        if (is_uploaded_file($this->tmpName)) {
            if (move_uploaded_file($this->tmpName, $this->pathToSaveFile . $this->fileName . "." . $this->fileExtension)) {
                return true;
            } else {
                $this->error[] = "File was not copied to server";
            }
        } else {
            $this->error[] = "File was not uploaded";
        }
        return false;
    }

    /**
     * Try the file size
     * 
     * @param float $maxSizeInMb Max file size enabled
     * @return boolean true if the file size respects the mas file size
     */
    private function fileSizeLessThan($maxSizeInMb) {
        if ($this->fileSize <= ($maxSizeInMb * 1024 * 1024)) {
            return true;
        }
        return false;
    }

    /**
     * Try the file extension
     * 
     * @param array() $extensionsEnabled list of all extensions enabled to upload
     * @return boolean true if the file extension is in the list
     */
    private function checkExtension($extensionsEnabled) {
        if (is_array($extensionsEnabled)) {
            if (in_array($this->fileExtension, $extensionsEnabled)) {
                return true;
            }
        } else if ($extensionsEnabled != null) {
            $this->error[] = "Enabled file extensions may be an array";
            return false;
        }
        return false;
    }

}
