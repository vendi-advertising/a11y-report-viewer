<?php

class TableMaker
{
    private $_json_as_string;

    private $_json_as_obj;

    private $_urls;

    private $_rules;

    private $_tags;

    private $_table;

    public function __construct(string $json)
    {
        $this->_json_as_string = $json;
    }

    public static function create_from_file(string $file_path) : self
    {
        $json = \file_get_contents($file_path);
        return new self($json);
    }

    protected function get_urls(bool $reload = false) : array
    {
        if(!$this->_urls || $reload){
            $raw_data = $this->_get_json_obj();

            $final_data = $raw_data->final_data;

            $urls = [];
            foreach($final_data as $idx => $d){
                $p = new Page();
                $p->url = $d->url;
                foreach(['violations', 'passes', 'incomplete', 'inapplicable'] as $k){
                    $value = $d->report->$k;
                    $p->$k = $value;
                }
                $urls[$d->url] = $p;
            }

            ksort($urls);

            $this->_urls = $urls;
        }

        return $this->_urls;
    }

    protected function get_rules(bool $reload = false) : array
    {
        if(!$this->_rules || $reload){
            $rules = [];
            foreach($this->get_urls() as $p){
                foreach(['violations', 'passes', 'incomplete', 'inapplicable'] as $thing){
                    foreach($p->$thing as $rule){
                        if(!array_key_exists($rule->id, $rules)){
                            $rr = new Rule();
                            foreach(['id', 'impact', 'tags', 'description', 'help', 'helpUrl'] as $k){
                                $rr->$k = $rule->$k;
                            }
                            $rules[$rule->id] = $rr;
                        }
                    }
                }
            }

            $this->_rules = $rules;
        }

        return $this->_rules;
    }

    protected function _get_json_obj(bool $reload = false) : stdClass
    {
        if(!$this->_json_as_obj || $reload){
            $this->_json_as_obj = json_decode($this->_json_as_string);
        }

        return $this->_json_as_obj;
    }

    public function get_tags(bool $reload = false) : array
    {
        if(!$this->_tags || $reload){
            $this->get_table();
        }

        return $this->_tags;
    }

    public function get_table(bool $reload = false)
    {
        if(!$this->_table || $reload){
            $all_tags = [];

            $buf = [];
            $buf[] = '<table>';
            $buf[] = '<thead>';
            $buf[] = '<tr>';
            $buf[] = '<td class="url">&nbsp;</td>';
            foreach($this->get_rules() as $rule){
                $buf[] = sprintf('<td class="rule-%2$s" data-rule-id="%2$s" title="%3$s"><p>%1$s</p></td>', htmlspecialchars($rule->id), htmlspecialchars($rule->id), htmlspecialchars($rule->description) );
            }
            $buf[] = '</tr>';
            $buf[] = '</thead>';
            $buf[] = '<tbody>';
            foreach($this->get_urls() as $url){
                $short = str_replace('https://www.vendiadvertising.com', '', $url->url);
                if(0 === strpos($short, '/external-redirect')){
                    continue;
                }
                if(0 === strpos(strrev($short), strrev('.pdf'))){
                    continue;
                }
                if(0 === strpos(strrev($short), strrev('.jpg'))){
                    continue;
                }
                if(0 === strpos(strrev($short), strrev('.gif'))){
                    continue;
                }
                if(0 === strpos(strrev($short), strrev('.png'))){
                    continue;
                }
                if($short === ''){
                    $short = '/';
                }
                $buf[] = '<tr>';
                $buf[] = sprintf('<td class="url"><a href="%1$s" target="_blank">%2$s</a></td>', $url->url, htmlspecialchars($short));

                foreach($this->get_rules() as $rule){
                    $rule_code = null;
                    $text_code = null;
                    $tags = null;

                    $things = [
                                'violations'   => '&#x2715;',
                                'passes'       => '&#x2714;',
                                'incomplete'   => '?',
                                'inapplicable' => 'n/a',
                        ];

                    foreach($things as $thing => $text){
                        foreach($url->$thing as $t){
                            if($t->id === $rule->id){
                                $rule_code = $thing;
                                $text_code = $text;
                                $tags = $t->tags;
                                break;
                            }
                        }
                        if($rule_code){
                            break;
                        }
                    }

                    $all_tags = array_merge($all_tags, $tags);

                    $buf[] = sprintf(
                                        '<td class="rule-%2$s" data-rule-id="%2$s" data-rule-value="%1$s" data-rule-tags="%4$s">%3$s</td>',
                                        htmlspecialchars($rule_code),
                                        htmlspecialchars($rule->id),
                                        $text_code,
                                        implode(' ', $tags)
                                    );
                }
                $buf[] = '</tr>';
            }
            $buf[] = '</tbody>';
            $buf[] = '<tfoot>';
            $buf[] = '<tr>';
            $buf[] = '<td>&nbsp;</td>';
            foreach($this->get_rules() as $rule){
                $buf[] = sprintf('<td data-rule-id="%2$s"></td>', htmlspecialchars($rule->id), htmlspecialchars($rule->id) );
            }
            $buf[] = '</tr>';
            $buf[] = '</tfoot>';
            $buf[] = '</table>';

            $all_tags = array_unique(array_values($all_tags));
            asort($all_tags);
            $all_tags = array_values($all_tags);

            $this->_tags = $all_tags;
            $this->_table = implode("\n", $buf);
        }

        return $this->_table;
    }
}
