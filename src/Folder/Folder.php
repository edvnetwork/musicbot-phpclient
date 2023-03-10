<?php
/**
 * Class Folder | src/Folder.php
 *
 * A single Folder with it's available actions
 *
 * @package      EDV_MusicBot
 * @author       Danilo Staender <edvnetwork.group@icloud.com>
 */

namespace EDV_Musicbot;

/**
 * Class Folder
 *
 * Folder represents a single Folder of the MusicBot
 */
class Folder extends RestClient
{
    /**
     * UUID holds the Folder UUID
     * @var array
     */
    public $uuid = null;
    /**
     * Folder stores the initial received folder data
     * @var array
     */
    private $folder = null;
    /**
     * Children stores the folder childrens
     * @var array
     */
    private $children = [];

    /**
     * __construct
     *
     * @param API $api MusicBot API
     * @param array $folder SiusBot Folder array
     */
    public function __construct($api, $folder)
    {
        parent::__construct($api);
        $this->uuid = $folder['uuid'];
        $this->folder = $folder;
    }

    /**
     * getTitle returns the title
     *
     * @return string foldername
     * @api
     */
    public function getTitle()
    {
        return $this->folder['title'];
    }

    /**
     * getType returns the file type
     *
     * @return string type: url, folder
     * @api
     */
    public function getType()
    {
        return array_key_exists('type', $this->folder) ? $this->folder['type'] : '';
    }

    /**
     * getUUID returns the uuid
     *
     * @return string folder UUID
     * @api
     */
    public function getUUID()
    {
        return $this->uuid;
    }

    /**
     * getUUID returns the uuid
     *
     * @return string folder UUID
     * @api
     */
    public function getParent()
    {
        return $this->folder["parent"];
    }

    /**
     * addChildrenIfOK checks recursive if the given file should be
     * added as a child element. Determined via the "parent" attribute
     *
     * @return File file
     * @api
     */
    public function addChildrenIfOK($file)
    {
        if ($this->getUUID() === $file->getParent()) {
            array_push($this->children, $file);
            return true;
        }
        foreach ($this->children as $children) {
            if ($children->getType() === "folder") {
                if ($children->addChildrenIfOK($file)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * getChildren returns the children of the folder
     *
     * @return (\File|\Folder)[]
     * @api
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * delete
     *
     * @return array status
     * @api
     */
    public function delete()
    {
        return $this->request('/bot/files/' . $this->uuid, 'DELETE');
    }

    /**
     * edit
     *
     * @param Array $options - keys: displayTitle, title, artist, album...
     * @return array status
     * @api
     */
    public function edit($options)
    {
        return $this->request('/bot/files/' . $this->uuid, 'PATCH', $options);
    }

    /**
     * move
     *
     * @param string $parent subfolder UUID, empty value means root folder
     * @return array status
     */
    public function move($parent = "")
    {
        return $this->request('/bot/files/' . $this->uuid, 'PATCH', [
            "parent" => $parent,
        ]);
    }

    /**
     * addFolder
     *
     * @param string $folderName folder name
     * @param string $parent subfolder UUID, empty value means root folder
     * @return array status
     */
    public function addFolder($folderName = "Folder", $parent = "")
    {
        return $this->request('/bot/folders', 'POST', [
            "name" => $folderName,
            "parent" => $parent,
        ]);
    }

    /**
     * moveFolder
     *
     * @param string $folderUUID folder uuid
     * @param string $parent subfolder UUID, empty value means root folder
     * @return array status
     */
    public function moveFolder($folderUUID, $parent = "")
    {
        return $this->moveTrack($folderUUID, $parent);
    }

    /**
     * renameFolder
     *
     * @param string $folderName Folder name
     * @param string $folderUUID uuid of the folder
     * @return array status
     */
    public function renameFolder($folderName, $folderUUID)
    {
        return $this->request('/bot/files/' . $folderUUID, 'PATCH', [
            "uuid" => $folderUUID,
            "type" => "folder",
            "title" => $folderName,
        ]);
    }

}
