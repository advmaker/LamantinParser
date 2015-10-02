<?php namespace Lamantin;

class Storage
{
    /**
     * @var string
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $file = $this->path . '/' . $name;

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

        return json_decode(file_get_contents($this->path . '/' . $name), true);
    }

    /**
     * @param string $name
     * @param mixed $data
     *
     * @return $this
     */
    public function put($name, $data)
    {
        file_put_contents($this->path . '/' . $name, json_encode($data));

        return $this;
    }
}
