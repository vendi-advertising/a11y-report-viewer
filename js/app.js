/*jslint white: true, browser: true, plusplus: true, esversion: 6*/

(function(window, document)
{
    const

        hide_stuff = function()
        {
            const
                url_count = document.querySelectorAll('tbody tr').length,
                classes = []
            ;

            document
                .querySelectorAll('thead td')
                .forEach(
                    (td) => {
                        if(parseInt(td.getAttribute('data-count-passes'), 10) === url_count){
                            classes.push('.rule-' + td.getAttribute('data-rule-id'));
                        }else if(parseInt(td.getAttribute('data-count-inapplicable'), 10) === url_count){
                            classes.push('.rule-' + td.getAttribute('data-rule-id'));
                        }
                    }
                )
            ;

            const
                selector = classes.join(','),
                text = selector + '{display: none;}',
                style = document.createElement('style')
            ;

            style.appendChild(document.createTextNode(text));
            document.head.appendChild(style);

            console.dir(style);
        },

        load = function()
        {
            document
                .querySelectorAll('thead td')
                .forEach(
                    (td) => {
                        const
                            rule_id = td.getAttribute('data-rule-id'),
                            violations = document.querySelectorAll('tbody td[data-rule-id=' + rule_id + '][data-rule-value=violations]').length,
                            passes = document.querySelectorAll('tbody td[data-rule-id=' + rule_id + '][data-rule-value=passes]').length,
                            inapplicable = document.querySelectorAll('tbody td[data-rule-id=' + rule_id + '][data-rule-value=inapplicable]').length,
                            incomplete = document.querySelectorAll('tbody td[data-rule-id=' + rule_id + '][data-rule-value=incomplete]').length
                        ;

                        td.setAttribute('data-count-violations', violations);
                        td.setAttribute('data-count-passes', passes);
                        td.setAttribute('data-count-inapplicable', inapplicable);
                        td.setAttribute('data-count-incomplete', incomplete);
                    }
                )
            ;
        },

        init = function()
        {
            window
                .addEventListener(
                    'DOMContentLoaded',
                    () => {
                        load();
                        hide_stuff();
                    }
                )
            ;
        }

    ;

    init();
}
(window, document));
