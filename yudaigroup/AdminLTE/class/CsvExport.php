<?php


class CsvExport
{
    private $conn;

    private $result;

    /**
     * CsvExport constructor.
     * @param $conn
     * @param $shop_id
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @return string
     */
    public function getCodeString()
    {
        return implode($this->code_array, ',');
    }

    public function setExportData($key_name, $codes)
    {
        $result = $this->result;
        foreach ((array)$codes as $code) {
            if ($code['code'] === '4111') {
                $result[10001][$key_name] = $code['value'];
            }

            if ($code['code'] === '5111' || $code['code'] === '5211') {
                $result[10002][$key_name] += $code['value'];
            }

            if ($code['code'] === '5311') {
                $result[10002][$key_name] -= $code['value'];
            }

            if ($code['type'] == 3) {
                $result[10004][$key_name] += $code['value'];
            }

            $result[$code['code']]['name'] = ($code['name']) ? $code['name'] : $result[$code['code']]['name'];
            $result[$code['code']][$key_name] = $code['value'];
        }
        $result[10003][$key_name] = $result[10001][$key_name] - $result[10002][$key_name];
        $result[10005][$key_name] = $result[10003][$key_name] - $result[10004][$key_name];

        $this->result = $result;
    }

    public function setExportDataSub($key_name, $codes)
    {
        $result = $this->result;
        foreach ((array)$codes as $code) {
            if ($code['type'] == 1) {
                if (in_array($code['code'], array('4115'))) {
                    $result[10001][$key_name] -= $code['value'];
                } else {
                    $result[10001][$key_name] += $code['value'];
                }
            }

            if ($code['type'] == 2) {
                // 期末在庫なので費用からマイナス
                if (in_array($code['code'], array('5213', '5273', '5311', '635'))) {
                    $result[10002][$key_name] -= $code['value'];
                } else {
                    $result[10002][$key_name] += $code['value'];
                }
            }


            if ($code['type'] == 3) {
                $result[10004][$key_name] += $code['value'];
            }

            if ($code['type'] == 4) {
                $result[10006][$key_name] += $code['value'];
            }

            if ($code['type'] == 5) {
                $result[10007][$key_name] += $code['value'];
            }

            $result[$code['code']]['name'] = ($code['name']) ? $code['name'] : $result[$code['code']]['name'];
            $result[$code['code']][$key_name] = $code['value'];
        }
        $result[10003][$key_name] = $result[10001][$key_name] - $result[10002][$key_name];
        $result[10005][$key_name] = $result[10003][$key_name] - $result[10004][$key_name];
        $result[10008][$key_name] = $result[10005][$key_name] + $result[10006][$key_name] - $result[10007][$key_name];

        $this->result = $result;
    }


    public function setExportAll($key_name, $shop_name, $codes)
    {
        $result = $this->result;
        foreach ((array)$codes as $code) {
            if ($code['type'] == 1) {
                if (in_array($code['code'], array('4115'))) {
                    $result[10001][$key_name][$shop_name] -= $code['value'];
                } else {
                    $result[10001][$key_name][$shop_name] += $code['value'];
                }
            }

            if ($code['type'] == 2) {
                // 期末在庫なので費用からマイナス
                if (in_array($code['code'], array('5213', '5273', '5311', '635'))) {
                    $result[10002][$key_name][$shop_name] -= $code['value'];
                } else {
                    $result[10002][$key_name][$shop_name] += $code['value'];
                }
            }

            if ($code['type'] == 3) {
                $result[10004][$key_name][$shop_name] += $code['value'];
            }

            if ($code['type'] == 4) {
                $result[10006][$key_name][$shop_name] += $code['value'];
            }

            if ($code['type'] == 5) {
                $result[10007][$key_name][$shop_name] += $code['value'];
            }
        }
        $result[10003][$key_name][$shop_name] = $result[10001][$key_name][$shop_name] - $result[10002][$key_name][$shop_name];
        $result[10005][$key_name][$shop_name] = $result[10003][$key_name][$shop_name] - $result[10004][$key_name][$shop_name];
        $result[10008][$key_name][$shop_name] =
            $result[10005][$key_name][$shop_name] + $result[10006][$key_name][$shop_name] - $result[10007][$key_name][$shop_name];

        $this->result = $result;
    }


    public function resetSum($key_name)
    {
        $this->result[10001][$key_name] = 0;
        $this->result[10002][$key_name] = 0;
        $this->result[10003][$key_name] = 0;
        $this->result[10004][$key_name] = 0;
        $this->result[10005][$key_name] = 0;
    }


    /**
     * @return mixed
     */
    public function getExportData()
    {
        return $this->result;
    }

}