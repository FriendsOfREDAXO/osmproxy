<?php 
rex_dir::delete($this->getDataPath());
rex_dir::copy($this->getPath('data'),$this->getDataPath());
