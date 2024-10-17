<?php

namespace JDecool\PHPStanReport\Generator;

use JDecool\PHPStanReport\Runner\PHPStanResultCache;
use NumberFormatter;

final class HtmlReportGenerator implements ReportGenerator
{
    public function __construct(
        private readonly NumberFormatter $formatter,
    ) {}

    public function generate(PHPStanResultCache $result, SortField $sortBy = SortField::Identifier): string
    {
        $html = <<<HTML
<html>
  <head>
    <title>PHPStan Report</title>
  </head>
  <body>
    <h1>PHPStan Report</h1>

    <table>
      <tr>
        <td><b>Total error(s)</b></td>
        <td>{$result->countTotalErrors()}</td>
      </tr>
      <tr>
        <td><b>Error(s)</b></td>
        <td>{$result->countErrors()}</td>
      </tr>
      <tr>
        <td><b>Locally ignored error(s)</b></td>
        <td>{$result->countLocallyIgnoredErrors()}</td>
      </tr>
    </table>

    <hr>

    <table>
      <thead>
        <tr>
          <th>Identifier</th>
          <th>Count</th>
        </tr>
      </thead>
      <tbody>
HTML;

        $errorsMap = $result->getErrorsMap();
        match ($sortBy) {
            SortField::Identifier => ksort($errorsMap),
            SortField::Counter => arsort($errorsMap),
        };

        foreach ($errorsMap as $identifier => $count) {
            $html .= "<tr><td>$identifier</td><td>{$this->formatter->format($count, NumberFormatter::DECIMAL)}</td></tr>";
        }

        $html .= <<<HTML
      </tbody>
      <tfoot>
        <tr>
          <th><b>Total</b></th>
          <td>{$result->countTotalErrors()}</td>
        </tr>
      </tfoot>
    </table>
  </body>
</html>
HTML;

        return $html;
    }

    public static function format(): string
    {
        return 'html';
    }
}
