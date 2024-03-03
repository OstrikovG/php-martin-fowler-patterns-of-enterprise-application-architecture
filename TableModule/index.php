<?php

/**
 * Provide statistics on the unique visitors data.
 * Implementation of a Table Module.
 */
class StatisticsModule
{
    /**
     * These rows can be the result of any query that conforms to the
     * schema defined by this Table Module. The real table may not even
     * exist, being substituted by views or a set of queries.
     */
    public function __construct(array $rows)
    {
        $this->_rows = $rows;
    }

    public function getMostPopularBrowser()
    {
        $browsers = array();
        foreach ($this->_rows as $row) {
            if (!isset($browsers[$row['browser']])) {
                $browsers[$row['browser']] = 0;
            }
            $browsers[$row['browser']]++;
        }
        arsort($browsers);
        reset($browsers);
        return current(array_keys($browsers));
    }

    /**
     * @param float $margin minimum percentual for considering a
     *                      resolution used by visitors
     */
    public function isResolutionUsed($resolution, $margin = 0.1)
    {
        $visitors = 0;
        foreach ($this->_rows as $row) {
            if ($row['resolution'] == $resolution) {
                $visitors++;
            }
        }
        return $visitors / count($this->_rows) > $margin;
    }
}

function create_row($browser, $resolution, $page)
{
    return array(
        'browser' => $browser,
        'resolution' => $resolution,
        'page' => $page
    );
}

// array is used for simplicity of stubbing here
// a RecordSet implementation will be more performant
$recordSet = array(
    create_row('MSIE', '1024x768', '/'),
    create_row('MSIE', '640x480', '/members'),
    create_row('MSIE', '1024x768', '/'),
    create_row('Firefox', '1280x1024', '/'),
    create_row('MSIE', '1024x768', '/'),
    create_row('Firefox', '1024x768', '/'),
    create_row('Firefox', '1024x768', '/'),
    create_row('Firefox', '1300x768', '/members'),
    create_row('Safari', '800x600', '/'),
    create_row('MSIE', '1024x768', '/members'),
    create_row('MSIE', '1024x768', '/members'),
    create_row('Chrome', '1024x768', '/members'),
    create_row('Chrome', '1280x1024', '/contacts'),
    create_row('Firefox', '1280x1024', '/'),
    create_row('MSIE', '124x768', '/about'),
);

// client code
$statisticsModule = new StatisticsModule($recordSet);
echo $statisticsModule->getMostPopularBrowser(), "\n";
var_dump($statisticsModule->isResolutionUsed('1024x768'));
var_dump($statisticsModule->isResolutionUsed('640x480'));