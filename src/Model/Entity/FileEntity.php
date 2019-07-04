<?php

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Upload
 * Define a type able to upload a file to server
 * 
 * @property string $fileEntityTmpName Temporary file's name
 * @property string $fileEntityName Currently file's name
 * @property string $fileEntityExtension File's extension
 * @property string $pathToSaveFileEntity Local to save file
 * @property float $fileEntitySize File's size in MB
 * @property array() $fileEntityErrors List of error messages
 */
class FileEntity extends Entity{

    /**
     * Returns an array with all error messages received
     * 
     * @return array()
     */
    public function getFileEntityErrors() {
        return $this->fileEntityErrors;
    }

    public function getFileEntityName(){
        return $this->id.".".$this->extension;
    }

    public function getExtension() {
        return $this->fileEntityExtension;
    }
    
    /**
     * Build a FileUpload object
     * 
     * @param type $_file Your $_FILES['file'] to be uploaded
     */
    public function setFile($_file){
        $this->fileEntityTmpName = $_file["tmp_name"];
        $this->fileEntityName = $_file['name'];
        $this->fileEntityExtension = strtolower(pathinfo($this->fileEntityName, PATHINFO_EXTENSION));
        $this->pathToSaveFileEntity = "";
        $this->fileEntitySize = $_file["size"];
        $this->fileEntityErrors = array();
    }

    /**
     * Enable to change the file name to be saved
     * 
     * @param string $fileName
     */
    public function setFileName($fileName) {
        $this->fileEntityName = $fileName;
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
        $this->pathToSaveFileEntity = WWW_ROOT . DS . $pathToSaveFile . DS;
    }

    /**
     * Enable test some features about the file like:
     * 
     * @param boolean $allowOverride       Enable override file
     * @param float $maxSizeInMb         Max file size enabled (in MB)
     * @param array() $extensionsEnabled   Extensions enabled to upload
     * @return boolean true if all validations were approved
     */
    public function validate($allowOverride = true, $maxSizeInMb = 0, $extensionsEnabled = null) {
        if (!$allowOverride) {
            if (file_exists($this->pathToSaveFileEntity . $this->fileEntityName)) {
                $this->fileEntityErrors[] = "File name '$this->fileEntityName' already exists";
                return false;
            }
        }
        if ($maxSizeInMb > 0) {
            if (!$this->fileSizeLessThan($maxSizeInMb)) {
                $this->fileEntityErrors[] = "File size greater than limit of " . $maxSizeInMb . "MB";
                return false;
            }
        }
        if ($extensionsEnabled) {
            if (!$this->checkExtension($extensionsEnabled)) {
                $this->fileEntityErrors[] = "File extension not enabled";
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
        if (is_uploaded_file($this->fileEntityTmpName)) {
            if (move_uploaded_file($this->fileEntityTmpName, $this->pathToSaveFileEntity . $this->fileEntityName . "." . $this->fileEntityExtension)) {
                return true;
            } else {
                $this->fileEntityErrors[] = "File was not copied to server";
            }
        } else {
            $this->fileEntityErrors[] = "File was not uploaded";
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
        if ($this->fileEntitySize <= ($maxSizeInMb * 1024 * 1024)) {
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
            if (in_array($this->fileEntityExtension, $extensionsEnabled)) {
                return true;
            }
        } else if ($extensionsEnabled != null) {
            $this->fileEntityErrors[] = "Enabled file extensions may be an array";
            return false;
        }
        return false;
    }

}
