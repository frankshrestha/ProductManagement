<?php

class ProductCtrl extends Product
{
    private $pid;
    private $name;
    private $sku;
    private $description;
    private $price;
    private $status;
    private $size;
    private $category;

    private $uploads_dir;

    private $sizeLimit;
    private $allowedKB;
    private $totalBytes;

    private $extension;
    private $errors;
    private $uploadedFiles;

    public function setProperties(?int $pid, $name, $sku, $description, $price, $status, $size, $category)
    {
        parent::__consruct();

        $this->pid = $pid;
        $this->name = $name;
        $this->sku = $sku;
        $this->description = $description;
        $this->price = $price;
        $this->status = $status;
        $this->size = $size;
        $this->category = $category;

        $this->uploads_dir = "../uploads/";

        $this->sizeLimit = 1024;
        $this->allowedKB = 100;
        $this->totalBytes = $this->allowedKB * $this->sizeLimit;

        $this->extension  = array('jpeg', 'jpg', 'png', 'gif');
        $this->errors = array();
        $this->uploadedFiles = array();
    }

    public function getPID()
    {
        parent::__consruct();
        return $this->getAllPID();
    }

    public function displayProduct($pid)
    {
        return $this->getProducts($pid);
    }

    public function getImg($pid)
    {
        return $this->getImages($pid);
    }

    public function validateUpload()
    {
        foreach ($_FILES['filesToUpload']['tmp_name'] as $key => $tmp_name) {
            $name = $_FILES['filesToUpload']['name'][$key];

            // Check for user selection
            if (empty($name)) {
                header('location: ../pages/create_product.php?error=noneSelected');
                exit();
            }

            // Check for valid size of selected images
            $this->checkSize($key, $name);

            // Check for valid image types
            $this->checkFileType($name);

            // Check if the images already exists in the server
            $this->checkExists($name);
        }

        // Check for any errors
        if (!$this->checkErrors()) {
            return false;
        }
        $this->Upload();
        return true;
    }

    public function createProduct()
    {
        $this->setProduct($this->name, $this->sku, $this->description, $this->price, $this->status, $this->size, $this->category, $this->uploadedFiles);
    }

    public function updatePrdct()
    {
        $this->updateProduct($this->pid, $this->name, $this->sku, $this->description, $this->price, $this->status, $this->size, $this->category, $this->uploadedFiles);
    }

    public function removeImgs()
    {
        $this->removeImages($this->pid);
    }

    private function checkSize($key, $name)
    {
        if ($_FILES["filesToUpload"]["size"][$key] > $this->totalBytes) {
            array_push($this->errors, $name . " file size is larger than 1 MB.");
        }
    }

    private function checkFileType($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (in_array($ext, $this->extension) == false) {
            array_push($this->errors, $name . " has invalid file type.");
        }
    }

    private function checkExists($name)
    {
        if (file_exists($this->uploads_dir . "/" . $name) == true) {
            array_push($this->errors, $name . " already exists.");
        }
    }

    private function checkErrors()
    {
        if (count($this->errors) > 0) {
            echo "<b>Errors:</b>";
            echo "<br/><ul>";
            foreach ($this->errors as $error) {
                echo "<li>" . $error . "</li>";
            }
            echo "</ul><br/>";

            $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
            echo "<a href='$url'>Go Back</a>";
            return false;
        }
        return true;
    }

    private function Upload()
    {
        foreach ($_FILES['filesToUpload']['tmp_name'] as $key => $tmp_name) {
            $temp = $_FILES['filesToUpload']['tmp_name'][$key];
            $name = $_FILES['filesToUpload']['name'][$key];

            move_uploaded_file($temp, $this->uploads_dir . "/" . $name);
            array_push($this->uploadedFiles, $name);
        }
    }

    public function uploadStatus()
    {
        if (count($this->uploadedFiles) > 0) {
            echo "<b>Uploaded Files:</b>";
            echo "<br/><ul>";
            foreach ($this->uploadedFiles as $fileName) {
                echo "<li>" . $fileName . "</li>";
            }
            echo "</ul><br/>";

            echo count($this->uploadedFiles) . " file(s) are successfully uploaded.<br><br>";

            $url = htmlspecialchars($_SERVER['HTTP_REFERER']);
            echo "<a href='$url'>Go Back</a>";
            echo 'Redirects in 5 seconds';
        }
    }
}
