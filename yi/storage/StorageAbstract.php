<?php

namespace yi\storage;

use support\exception\Exception;
use app\system\model\admin\AttachmentModel;
use Webman\Http\UploadFile;

class StorageAbstract
{
    protected $options = [
        'type' => 'public',
        'group' => 'default',
        'scene' => 'system',
        'accept' => '*',
        'maxsize' => 10 * 1024 * 1024,
        'record' => true
    ];

    public function init(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        return $this;
    }

    public function upload($file)
    {
        $chunkUpload = false;
        if (request()->post('chunk_id')) {
            $chunkdir = runtime_path() . DS . 'storage' . DS . 'chunks' . DS;
            if (!is_dir($chunkdir)) @mkdir($chunkdir, 0755, true);
            $chunk_id = request()->post('chunk_id');
            $index = request()->post('index');
            $count = request()->post('count');
            $name = request()->post('name');
            $mimeType = request()->post('mime_type');
            $thunkname = $chunk_id . '-' . $index . '.part';
            $file->move($chunkdir . $thunkname);
            if ($index < $count - 1) return 'continue';
            $finish = true;
            for($i = 0 ; $i < $count; $i ++) {
                if (!file_exists($chunkdir . $chunk_id . '-' . $i . '.part')) {
                    $finish = false;
                    break;
                }
            }
            if (!$finish) throw new Exception("Chunk file upload error");
            $tmpdir = runtime_path() . DS . 'storage' . DS . 'temp' . DS . date('Ymd') . DS;
            if (!is_dir($tmpdir)) mkdir($tmpdir, 0755, true);
            $filepath = $tmpdir . $chunk_id . '.' . pathinfo($name, PATHINFO_EXTENSION);
            $out = fopen($filepath, 'wb');

            if (flock($out, LOCK_EX)) {
                for ($i = 0; $i < $count; $i ++) {
                    $partFile = $chunkdir . $chunk_id . '-' . $i . '.part';
                    fwrite($out, file_get_contents($partFile));
                    @unlink($partFile);
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);
            $file = new UploadFile($filepath, $name, $mimeType, 400);
            $chunkUpload = true;
        }
        $ext = $file->getUploadExtension();
        $accept = explode(',', $this->option('accept'));
        $file_md5 = hash_file('md5', $file->getPathname());
        $file_sha1 = hash_file('sha1', $file->getPathname());
        $file_size = $file->getSize();
        if (!in_array('*', $accept) && !in_array($ext, $accept)) {
            @unlink($file->getRealPath());
            throw new Exception(lang("File type is not supported"));
        }
        if ($this->option('maxsize') != 0 && $this->option('maxsize') < $file_size) {
            @unlink($file->getRealPath());
            throw new Exception(lang("File size out of limit"));
        }
        if (!$this->option('record')) {
            $savepath = config('storage.drivers.local.' . $this->option('type') . '.path') . DS . date('Ym') . DS . date('dd') . $file_sha1 . '.'. $file->getUploadExtension();
            $file->move($savepath);
            return $savepath;
        }
        $admin = get_admin();
        $user = get_user();
        $data = AttachmentModel::where('sha1', $file_sha1)->where('type', $this->option('type'))->first();
        if (!empty($data)){
            if ($chunkUpload) @unlink($file->getRealPath());
            return $data->url;
        }
        $payload = (object)[
            'file' => $file,
            'md5' => $file_md5, 
            'sha1' => $file_sha1
        ];
        $url = $this->afterUpload($payload);
        $form = [
            'name' => $file->getUploadName(),
            'type' => $this->option('type'),
            'scene' => $this->option('scene'),
            'storage' => $this->option('driver'),
            'group' => $this->option('group'),
            'mimetype' => $file->getUploadMineType(),
            'ext' => $ext,
            'md5' => $file_md5, 
            'sha1' => $file_sha1,
            'filesize' => $file_size,
            'url' => $url,
            'uploadtime' => time(),
            'admin_id' => $admin->id ?? 0,
            'user_id' => $user->id ?? 0
        ];
        AttachmentModel::create($form);
        return $url;
    }
    
    public function option()
    {
        $args = func_get_args();
        if (count($args) == 1) {
            if (is_array($args[0])) {
                $this->options = array_merge($this->options, $args[0]);
                return $this;
            } elseif (is_string($args[0])) {
                return $this->options[$args[0]];
            }
        } elseif (count($args) == 2) {
            $this->options[$args[0]] = $args[1];
            return $this;
        }
        return $this;
    }

    public function getFilePath($attachment)
    {}

    public function getUrl($attachment)
    {}

    public function delete($attachment)
    {}
}