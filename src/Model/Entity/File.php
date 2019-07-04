<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * File Entity
 *
 * @property int $id
 * @property string $extension
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime|null $modified
 * 
 * @property \App\Model\Entity\User[] $users
 * @property FileEntity $_file File to be uploaded
 */
class File extends Entity
{
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
        'extension' => true,
        'created' => true,
        'modified' => true,
        'users' => true,
        'file' => true
    ];
    
    public function getFileName(){
        return $this->id.".".$this->extension;
    }

    public function _setFile($_file) {
        $this->_file = new FileEntity($_file);
        $this->extension = $this->_file->getExtension();
    }
    
    public function upload() {
        $this->_file->setFileName($this->id);
        $this->_file->setPathToSaveFile('img');
        $this->_file->upload();
    }
}
