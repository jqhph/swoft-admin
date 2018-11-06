<?php

namespace Swoft\Admin\Grid\Exporters;

use Psr\Http\Message\ResponseInterface;
use Swoft\Core\RequestContext;
use Swoft\Http\Message\Stream\SwooleStream;
use Swoft\Support\Collection;
use Swoft\Support\Str;

class CsvExporter extends AbstractExporter
{
    protected $title = [];

    /**
     * 设置标题
     *
     * @param array $title
     * @return $this
     */
    public function title(array $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * 执行导出操作
     *
     * @param \Closure|null $filter
     * @return ResponseInterface
     */
    public function export(\Closure $filter = null)
    {
        $filename = $this->getFilename().'.csv';

        $records = $this->getData();

        if ($filter) {
            $records = $filter($records);
        }

        return RequestContext::getResponse()
            ->withoutHeader('Content-Encoding')
            ->withHeader('Content-Type', 'text/csv; charset=utf-8')
            ->withHeader('Content-Disposition', "attachment; filename=\"$filename\"")
            ->withBody(new SwooleStream($this->buildCsvContent($records)));
    }

    /**
     * @param Collection $records
     * @return string
     */
    protected function buildCsvContent(Collection $records)
    {
        $titles = $this->title ?: [];

        ob_start();
        $handle = fopen('php://output', 'w');

        if ($records->count()) {
            if (empty($titles)) {
                $titles = $this->getHeaderRowFromRecords($records);
            }
            // Add CSV headers
            $this->putcsv($handle, $titles);

            foreach ($records as &$record) {
                $this->putcsv($handle, $this->getFormattedRecord($record));
            }
        }

        $content = ob_get_clean();

        // Close the output stream
        fclose($handle);

        return $content;
    }

    /**
     * @param $handle
     * @param array $data
     * @param string $d
     */
    protected function putcsv($handle, array $data, string $d = ',')
    {
        fwrite($handle, implode($d, $data)."\n");
    }

    /**
     * @param Collection $records
     *
     * @return array
     */
    public function getHeaderRowFromRecords(Collection $records): array
    {
        $titles = collect(array_dot($records->first()))->keys()->map(
            function ($key) {
                $key = str_replace('.', ' ', $key);

                return Str::ucfirst($key);
            }
        );

        return $titles->toArray();
    }

    /**
     * @param array $record
     * @return array
     */
    public function getFormattedRecord($record)
    {
        return array_dot($record);
    }
}
