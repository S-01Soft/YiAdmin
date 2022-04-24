<?php

namespace yi\storage;

interface StorageInterface
{
    public function getFilePaht($attachment);

    public function getUrl($attachment);

    public function delete($attachment);
}