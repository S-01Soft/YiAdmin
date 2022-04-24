<?php

namespace yi\storage;

class Local extends StorageAbstract implements StorageInterface
{
    public function afterUpload($payload)
    {
        $file = $payload->file;
        $type = $this->option('type');
        $path = date('Ym') . DS . date('d') . DS . $payload->sha1 . '.'. $file->getUploadExtension();
        $savepath = config('storage.drivers.local.' . $type . '.path') . DS . $path;
        $file->move($savepath);
        return str_replace('\\', '/', config('storage.drivers.local.' . $type . '.url') . '/' . $path);
    }

    public function getFilePath($attachment)
    {
        if ($attachment['type'] == 'public') $file = public_path() . DS . $attachment['url'];
        else $file = runtime_path() . DS . $attachment['url'];
        $file = str_replace(['/', '\\'], [DS, DS], $file);
        $file = preg_replace("/\\" . DS . "+/", DS, $file);
        return $file;
    }

    /**
     * 获取可访问的Url
     */
    public function getUrl($attachment)
    {
        if ($attachment['type'] == 'public') return fixurl('/' . $attachment['url']);
        return '';
    }

    public function delete($attachment) 
    {
        $file = $this->getFilePath($attachment);
        @unlink($file);
    }
}