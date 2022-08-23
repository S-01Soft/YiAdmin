<?php

namespace yi\storage;

interface StorageInterface
{
    public function getFilePath($attachment);

    public function getUrl($attachment);

    public function delete($attachment);
}