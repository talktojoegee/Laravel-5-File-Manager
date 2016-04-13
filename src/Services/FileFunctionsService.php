<?php
namespace Infinety\FileManager\Services;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Image
 * @package App\Services\ImageUploadService
 */
class FileFunctionsService
{

    /**
     * Home Path
     * @var
     */
    protected $path;

    /**
     * FileUploadService constructor.
     */
    public function __construct()
    {
        $this->path = config('filemanager.homePath');
    }

    /**
     * Handles Upload File Method
     * @param UploadedFile|null $file
     * @param $folder
     * @return stringch
     */
    public function uploadFile(UploadedFile $file = null, $folder){
        $result = $this->upload($file, $folder);
        return $result;
    }

    /**
     * Creates new folder on path
     * @param $newName
     * @param $currentFolder
     * @return array
     */
    public function createFolder($newName, $currentFolder){

        $path = $this->path.DIRECTORY_SEPARATOR.$currentFolder.DIRECTORY_SEPARATOR;
        if(!is_writable($path)){
            return ['error' => 'This folder is not writable'];
        }

        if($this->checkFolderExists($path.$newName)){
            return ['error' => 'This folder already exists'];
        }

        try{
            mkdir($path.$newName, 0755);
            return ['success' => 'Folder '.$newName.' created successfully'];
        } catch(\Exception $e) {
            return ['error' => 'Error creating folder'];
        }
    }

    /**
     * Deletes a file or Folder
     * @param $data
     * @param $folder
     * @param $type
     * @return array
     */
    public function delete($data, $folder,  $type){
        if($type == 'folder'){
            try{
                $folder = rtrim($this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$data,"/");
                $this->deleteFolderRecursive($folder);
                return ['success' => 'Folder '.$data.' deleted successfully'];
            } catch(\Exception $e) {
                return ['error' => 'Error deleting folder'];
            }
        }

        if($type == 'file'){
            try{

                unlink($this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR.$data);
                return ['success' => 'File '.$data.' deleted successfully'];
            } catch(\Exception $e) {
                return ['error' => 'Error deleting file'];
            }
        }
    }



    private function deleteFolderRecursive($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object))
                        $this->deleteFolderRecursive($dir."/".$object);
                    else
                        unlink($dir."/".$object);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Handles Upload files
     *
     * @param UploadedFile $file
     * @param $folder
     * @return stringch
     */
    private function upload(UploadedFile $file, $folder){

        $originalName = $file->getClientOriginalName();

        $originalNameWithoutExt = substr($originalName, 0, strlen($originalName) - 4);
        $newName = $this->sanitize($originalNameWithoutExt).".".$file->getClientOriginalExtension();
        $path = $this->path.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;

        $this->checkFileExists($path,$newName);

        $name = (!$this->checkFileExists($path,$newName)) ? $newName : $this->checkFileExists($path,$newName);

        if(!is_writable($path)){
            return ['error' => 'This folder is not writable'];
        }

        if($file->move($path, $name)){
            return ['success' => $name];
        } else {
            return ['error' => 'Impossible ti upload this file to this folder'];
        }

    }


    /**
     * Check if folder exists
     *
     * @param $folder
     * @return bool
     */
    private function checkFolderExists($folder){
        if(file_exists($folder)){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if file is on server and returns the name of file plus counter
     *
     * @param $folder
     * @param $name
     * @return bool|string
     */
    private function checkFileExists($folder, $name){

        if(file_exists($folder.$name)){
            $withoutExt = pathinfo($name, PATHINFO_FILENAME);
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $i = 1;
            while(file_exists($folder.$withoutExt."-".$i.".".$ext)) {
                $i++;
            }
            return $withoutExt."-".$i.".".$ext;
        }
        return false;
    }

    /**
     * @param $string
     * @param bool $force_lowercase
     * @param bool $anal
     * @return bool|mixed|string
     */
    private function sanitize($string, $force_lowercase = false, $anal = true)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "-", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "-", $clean) : $clean ;

        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }

}