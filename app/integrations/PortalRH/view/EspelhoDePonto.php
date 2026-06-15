<?php

declare(strict_types=1);

namespace Holerito\integrations\PortalRH\view;

final class EspelhoDePonto
{
      public function make(array $espelho): array
      {
            $dias = $espelho['Dias'] ?? [];
            $totais = $espelho['Totais'] ?? [];
            $contrato = $espelho['Contrato'] ?? [];

            $totaisFormatados = [];

            foreach ($totais as $total) {
                  $totaisFormatados[$total['Descricao']] = $total['Valor'];
            }

            $diasFormatados = array_map(function ($dia) {

                  $marcacoes = preg_split(
                        '/\s+/',
                        trim($dia['Marcacoes'])
                  );

                  $entrada = $marcacoes[0] ?? '--:--';
                  $saidaAlmoco = $marcacoes[1] ?? '--:--';
                  $voltaAlmoco = $marcacoes[2] ?? '--:--';
                  $saida = $marcacoes[3] ?? '--:--';

                  $workedMinutes = 0;

                  if (count($marcacoes) >= 4) {

                        $morning =
                              $this->toMinutes($saidaAlmoco)
                              -
                              $this->toMinutes($entrada);

                        $afternoon =
                              $this->toMinutes($saida)
                              -
                              $this->toMinutes($voltaAlmoco);

                        $workedMinutes =
                              $morning + $afternoon;
                  }

                  // 8h48 = 528 minutos
                  $expectedMinutes = 528;

                  $saldoMinutes =
                        $workedMinutes - $expectedMinutes;

                  return [
                        'date' => $dia['DataJson'],
                        'week_day' => $dia['DiaDaSemana'],
                        'type' => $dia['TipoDia'],

                        'entrada' => $entrada,
                        'saida_almoco' => $saidaAlmoco,
                        'volta_almoco' => $voltaAlmoco,
                        'saida' => $saida,

                        'tipoDia' => $dia['TipoDia'] ?? null,

                        // NOVOS DADOS

                        'total' => $this->minutesToHours(
                              $workedMinutes
                        ),

                        'saldo' => ($saldoMinutes >= 0 ? '+' : '-')
                              .
                              $this->minutesToHours(
                                    abs($saldoMinutes)
                              ),

                        'saldo_type' =>
                        $saldoMinutes >= 0
                              ? 'positive'
                              : 'negative',
                  ];
            }, $dias);

            $salary = $contrato['SalarioContratual'] ?? 0;

            $perHour = $salary / MONTH_DIVISOR;

            $delayHours = hoursToDecimal(
                  $totaisFormatados['Atrasos'] ?? '0,00'
            );

            $extraHours = hoursToDecimal(
                  $totaisFormatados['Horas Extras 50%'] ?? '0,00'
            );

            $delayDiscount = $delayHours * $perHour;

            $extraTotal = $extraHours * ($perHour * 1.5);

            $benefitDiscount = $salary * BENEFIT_DISCOUNT;
            $liquidProjection =
                  $salary
                  + $extraTotal
                  - $delayDiscount
                  - $benefitDiscount
                  - INSS
                  - DENTIST
                  - MEAL_VOUCHER
                  - TRANSPORT_VOUCHER;

            $salaryDiff = $extraTotal - $delayDiscount;
            $formattedDiff =
                  ($salaryDiff >= 0 ? '+' : '-')
                  . __moneyFormatter(abs($salaryDiff));

            return [
                  'employee' => [
                        'salary' => $salary,
                        'salary_diff' => [
                              'value' => $salaryDiff,
                              'formatted' => $formattedDiff,
                              'is_positive' => $salaryDiff >= 0
                        ],
                        'liquid_salary' => $liquidProjection ?? 0,
                        'per_hour' => $perHour ?? 0,
                        'benefit' => $benefitDiscount ?? 0,
                        'role' => $contrato['Cargo']['Descricao'] ?? null,
                        'hours' => $contrato['HorasContratuais'] ?? 0,
                  ],

                  'totals' => [
                        'extras_50' => $totaisFormatados['Horas Extras 50%'] ?? '0,00',
                        'atrasos' => $totaisFormatados['Atrasos'] ?? '0,00',
                        'faltas' => $totaisFormatados['Faltas Não Justif.'] ?? '0,00',
                        'repouso_perdido' => $totaisFormatados['Repouso Perdido'] ?? '0,00',
                  ],

                  'days' => $diasFormatados
            ];
      }

      private function toMinutes(string $time): int
      {
            [$hours, $minutes] = explode(':', $time);

            return ($hours * 60) + $minutes;
      }

      private function minutesToHours(int $minutes): string
      {
            $hours = floor($minutes / 60);

            $mins = $minutes % 60;

            return sprintf(
                  '%02d:%02d',
                  $hours,
                  $mins
            );
      }
}
