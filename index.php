<?php

use Webmozart\PathUtil\Path;

define('VENDI_A11Y_FILE', __FILE__);
define('VENDI_A11Y_DIR', __DIR__ );

require_once VENDI_A11Y_DIR . '/vendor/autoload.php';

$report_file = 'FILE_PATH_HERE'

$file_data = file_get_contents($report_file);

$raw_data = json_decode($file_data);

$final_data = $raw_data->final_data;

class Page
{
    public $url;

    public $violations;

    public $passes;

    public $incomplete;

    public $inapplicable;
}

$urls = [];
foreach($final_data as $idx => $d){
    $p = new Page();
    $p->url = $d->url;
    foreach(['violations', 'passes', 'incomplete', 'inapplicable'] as $k){
        $value = $d->report->$k;
        $p->$k = $value;
    }
    $urls[] = $p;
}

class Rule
{
    public $id;             //": "aria-allowed-role"
    public $impact;         //": null
    public $tags;           //": array:2 [▶]
    public $description;    //": "Ensures role attribute has an appropriate value for the element"
    public $help;           //": "ARIA role must be appropriate for the element"
    public $helpUrl;        //": "https://dequeuniversity.com/rules/axe/3.1/aria-allowed-role?application=axeAPI"
}

$rules = [];
foreach($urls as $p){
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

echo '<!doctype html>';
echo '<html lang="en">';
echo '<head>';
echo '<title>Report</title>';
echo '<link rel="stylesheet" href="./css/app.css" />';
echo '</head>';
echo '<body>';
echo '<table>';
echo '<thead>';
echo '<tr>';
echo '<td class="url">&nbsp;</td>';
foreach($rules as $rule){
    echo sprintf('<td class="rule-%2$s" data-rule-id="%2$s">%1$s</td>', htmlspecialchars($rule->id), htmlspecialchars($rule->id) );
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';
foreach($urls as $url){
    $short = str_replace('https://www.DOMAIN.com', '', $url->url);
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
    echo '<tr>';
    echo sprintf('<td class="url"><a href="%1$s" target="_blank">%2$s</a></td>', urlencode($url->url), htmlspecialchars($short));

    foreach($rules as $rule){
        $rule_code = null;
        $text_code = null;

        $things = [
                    'violations' => '&#x2715;',
                    'passes' => '&#x2714;',
                    'incomplete' => '?',
                    'inapplicable' => 'n/a',
            ];

        foreach($things as $thing => $text){
            foreach($url->$thing as $t){
                if($t->id === $rule->id){
                    $rule_code = $thing;
                    $text_code = $text;
                    break;
                }
            }
            if($rule_code){
                break;
            }
        }

        echo sprintf('<td class="rule-%2$s" data-rule-id="%2$s" data-rule-value="%1$s">%3$s</td>', htmlspecialchars($rule_code), htmlspecialchars($rule->id), $text_code);
    }
    echo '</tr>';
}
echo '</tbody>';
echo '<tfoot>';
echo '<tr>';
echo '<td>&nbsp;</td>';
foreach($rules as $rule){
    echo sprintf('<td data-rule-id="%2$s"></td>', htmlspecialchars($rule->id), htmlspecialchars($rule->id) );
}
echo '</tr>';
echo '</tfoot>';
echo '</table>';
echo '<script type="text/javascript" src="./js/app.js"></script>';
echo '</body>';
echo '</html>';
