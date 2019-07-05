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
 * @property array() $fileErrors List of error messages
 */
class File extends Entity {

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'id' => true,
        'filename' => true,
        'file' => true
    ];

    /**
     * Returns an array with all error messages received
     * 
     * @return array()
     */
    public function getFileErrors() {
        return $this->fileErrors;
    }

    public function getFileName() {
        return $this->id . "." . $this->extension;
    }

    public function getExtension() {
        return $this->extension;
    }

    /**
     * @param type $_file Your $_FILES['file'] to be uploaded
     */
    public function _setFile($_file) {
        $this->tmpName = $_file["tmp_name"];
        $this->extension = strtolower(pathinfo($_file["name"], PATHINFO_EXTENSION));
        $this->pathToSaveFile = "";
        $this->fileSize = $_file["size"];
        $this->fileErrors = array();
    }

    /**
     * Enable to set where the file may be saved
     * <p>
     * Make sure, <em>DO NOT</em> to put a slash "/" at the begin <em>OR</em> the end of path
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
     * @param float $maxSizeInMb           Max file size enabled (in MB)
     * @param array() $extensionsEnabled   Extensions enabled to upload
     * 
     * @return boolean true if all validations were approved
     */
    public function validate($allowOverride = true, $maxSizeInMb = 0, $extensionsEnabled = null) {
        if (!is_uploaded_file($this->tmpName)) {
            $this->fileErrors[] = "File not uploaded";
            return false;
        }
        if (!$allowOverride) {
            if (file_exists($this->pathToSaveFile . $this->fileName)) {
                $this->fileErrors[] = "File name '$this->fileName' already exists";
                return false;
            }
        }
        if ($maxSizeInMb > 0) {
            if (!$this->fileSizeLessThan($maxSizeInMb)) {
                $this->fileErrors[] = "File size greater than limit of " . $maxSizeInMb . "MB";
                return false;
            }
        }
        if ($extensionsEnabled) {
            if (!$this->checkExtension($extensionsEnabled)) {
                $this->fileErrors[] = "File extension not enabled";
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
    public function upload($pathToSaveFile) {
        $this->setPathToSaveFile($pathToSaveFile);

        if (move_uploaded_file($this->tmpName, $this->pathToSaveFile . $this->id . "." . $this->extension)) {
            return true;
        } else {
            $this->fileErrors[] = "File was not copied to server";
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
            if (in_array($this->extension, $extensionsEnabled)) {
                return true;
            }
        } else if ($extensionsEnabled != null) {
            $this->fileErrors[] = "Enabled file extensions may be an array";
            return false;
        }
        return false;
    }

}
