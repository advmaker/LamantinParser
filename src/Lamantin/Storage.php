<?php namespace Lamantin;

class Storage
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $extension;

    /**
     * @param string $path
     * @param string $extension
     */
    public function __construct($path, $extension = '.file')
    {
        $this->path = $path;
        $this->extension = $extension;

        if (!file_exists($this->path)) {
            mkdir($this->path);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $file = $this->path . '/' . $name . $this->extension;

        return file_exists($file) && is_file($file);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            return null;
        }

        return json_decode(file_get_contents($this->path . '/' . $name . $this->extension), true);
    }

    /**
     * @param string $name
     * @param mixed $data
     *
     * @return $this
     */
    public function put($name, $data)
    {
        file_put_contents($this->path . '/' . $name . $this->extension, json_encode($data));

        return $this;
    }
}
