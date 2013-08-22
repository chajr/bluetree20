<?php/** * simple rss help class * * @category    BlueFramework * @package     misc * @author      Michał Adamiak    <chajr@bluetree.pl> * @copyright   chajr/bluetree * @version     1.0.0 */class rss    extends DOMDocument{    protected $_chanel = NULL;    /**     * create xml RSS content     *     * @param string $copy     * @param string $pubdate     * @param string $managingEditor     * @param string $webMaster     * @param string $ttl     */    public function __construct(        $copy,        $pubdate,        $managingEditor,        $webMaster,        $ttl    ){        parent::__construct('1.0', 'UTF-8');        $this->formatOutput = TRUE;        $root = $this->createElement('rss');        $root = $this->appendChild($root);        $root->setAttribute('version', '2.0');        $chanel         = $this->createElement('channel');        $copyright      = $this->createElement('copyright', $copy);        $date           = $this->createElement('pubDate', $pubdate);        $mail           = $this->createElement('managingEditor', $managingEditor);        $adminMail      = $this->createElement('webMaster', $webMaster);        $lifeTime       = $this->createElement('ttl', $ttl);        $this->_chanel = $root->appendChild($chanel);        $this->_chanel->appendChild($copyright);        $this->_chanel->appendChild($date);        $this->_chanel->appendChild($mail);        $this->_chanel->appendChild($adminMail);        $this->_chanel->appendChild($lifeTime);    }    /**     * create main content element     *     * @param string $title     * @param string $url     * @param string $description     */    public function main($title, $url, $description)    {        $title          = $this->_stringConversion($title);        $description    = $this->_stringConversion($description);        $title          = $this->createElement('title', $title);        $url            = $this->createElement('link', $url);        $description    = $this->createElement('description', $description);        $this->_chanel->appendChild($title);        $this->_chanel->appendChild($url);        $this->_chanel->appendChild($description);    }    /**     * create sub elements     *     * @param string $title     * @param string $url     * @param string $description     */    public function add($title, $url, $description)    {        $title          = $this->_stringConversion($title);        $description    = $this->_stringConversion($description);        $item           = $this->createElement('item');        $title          = $this->createElement('title', $title);        $url            = $this->createElement('link', $url);        $description    = $this->createElement('description', $description);        $this->_chanel->appendChild($title);        $this->_chanel->appendChild($url);        $this->_chanel->appendChild($description);        $this->_chanel->appendChild($item);    }    /**     * convert string to secure some special chars     *     * @param string $content     * @return string     */    protected function _stringConversion($content)    {        return htmlspecialchars($content);    }}