<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Imageutils
{

    private $CI;

    public function __construct()
    {
	$this->CI = & get_instance();
    }

    public function resize($source = NULL, $width = NULL, $height = NULL)
    {

		// get source image size
		$size = getimagesize($source);
		$size['height'] = $size[1];
		$size['width'] = $size[0];

		// check is at least final size
		if ($size['width'] < $width || $size['height'] < $height)
		{
		    //return 'Image must be at least ' . $width . 'x' . $height . ' pixels';
		}

		// all ok, resize if bigger
		if ($size['width'] > $width || $size['height'] > $height)
		{
		    // resize depending on which side smaller of target image
		    if (($size['height']/$size['width']) < ($height/$width))
		    {
			$image_config['height'] = 4000;
			$image_config['width'] = $width;
		    } else
		    {
			$image_config['height'] = $height;
			$image_config['width'] = 4000;
		    }

		    $image_config['maintain_ratio'] = TRUE;

		    // set source
		    $image_config['source_image'] = $source;

		    $this->CI->load->library('image_lib', $image_config);

		    // reload config again in case running more than once in same script
		    $this->CI->image_lib->clear();
		    $this->CI->image_lib->initialize($image_config);

		    // attempt resize
		    if (!$this->CI->image_lib->resize())
		    {
				return 'Image could not be resized';
		    }
		}
	}

    public function resize_and_crop($source = NULL, $width = NULL, $height = NULL)
    {

		// get source image size
		$size = getimagesize($source);
		$size['height'] = $size[1];
		$size['width'] = $size[0];

		// check is at least final size
		if ($size['width'] < $width || $size['height'] < $height)
		{
		    //return 'Image must be at least ' . $width . 'x' . $height . ' pixels';
		}

		// all ok, resize if bigger
		if ($size['width'] > $width || $size['height'] > $height)
		{
		    // resize depending on which side bigger of target image
		    if (($size['height']/$size['width']) > ($height/$width))
		    {
			$image_config['height'] = 4000;
			$image_config['width'] = $width;
		    } else
		    {
			$image_config['height'] = $height;
			$image_config['width'] = 4000;
		    }

		    $image_config['maintain_ratio'] = TRUE;

		    // set source
		    $image_config['source_image'] = $source;

		    $this->CI->load->library('image_lib', $image_config);

		    // reload config again in case running more than once in same script
		    $this->CI->image_lib->clear();
		    $this->CI->image_lib->initialize($image_config);

		    // attempt resize
		    if (!$this->CI->image_lib->resize())
		    {
				echo $this->CI->image_lib->display_errors();
				phpinfo();
			return 'Image could not be resized';
		    }

		    // set new vars ready for crop
		    $image_config['height'] = $height;
		    $image_config['width'] = $width;
		    $image_config['maintain_ratio'] = FALSE;

		    // get new size
		    $size = getimagesize($source);
		    $size['height'] = $size[1];
		    $size['width'] = $size[0];

		    // if source height is same as target height, crop on x axis only
		    if ($size['height'] === $height)
		    {
			$image_config['x_axis'] = round(($size['width'] - $width) / 2);
			$image_config['y_axis'] = 0;
		    } else
		    {
			$image_config['x_axis'] = 0;
			$image_config['y_axis'] = round(($size['height'] - $height) / 2);
		    }

		    // update config
		    $this->CI->image_lib->clear();
		    $this->CI->image_lib->initialize($image_config);

		    // attempt crop
		    if (!$this->CI->image_lib->crop())
		    {
			return 'Image could not be cropped';
		    }
		}

		// all ok
		return TRUE;
    }

    public function recursive_delete($path)
    {
		if (!file_exists($path))
		{
		    return TRUE;
		}

		$directoryIterator = new DirectoryIterator($path);

		foreach ($directoryIterator as $fileInfo)
		{
		    $filePath = $fileInfo->getPathname();
		    if (!$fileInfo->isDot())
		    {
			if ($fileInfo->isFile())
			{
			    unlink($filePath);
			} elseif ($fileInfo->isDir())
			{
			    if ($this->emptyDirectory($filePath))
			    {
				rmdir($filePath);
			    } else
			    {
				$this->recursive_delete($filePath);
			    }
			}
		    }
		}
		rmdir($path);

		return TRUE;
    }

}

/* End of file Imageutils.php */
