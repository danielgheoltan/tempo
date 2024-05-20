<?php
$logRowTemplate = <<<HTML
    <tr class="log" data-started="" data-synced="false">
        <td data-for="db_status">
            <div class="icon icon--db-status"
                 data-name="db_status"
            >
                <svg viewBox="0 0 32 32" width="16" height="16">
                    <use xlink:href="images/sprite.svg#db-status" />
                </svg>
            </div>
            <div class="icon icon--db-delete tooltip"
                 data-name="db_delete"
                 data-title="{$_ENV['i18n']('Delete')}"
                 onclick="APP.ButtonDb.onclick(event)"
                 style="display: none;"
            >
                <svg viewBox="0 0 32 32" width="16" height="16">
                    <use xlink:href="images/sprite.svg#db-delete" />
                </svg>
            </div>
        </td>
        <td data-for="jira_status">
            <div class="icon icon--jira-status tooltip"
                 data-name="jira_status"
                 data-title="{$_ENV['i18n']('Save')}"
                 onclick="APP.ButtonJira.onclick(event)"
                 style="--tooltip-delta-y: 4px;"
            > 
                <svg viewBox="0 0 32 32" width="16" height="16">
                    <use xlink:href="images/sprite.svg#jira-status" />
                </svg>
            </div>
        </td>
        </td>
        <td colspan="2">
            <label class="input-shell">
                <input type="text"
                       data-name="issue_key"
                       data-value=""
                       onblur="APP.Input.onblur(this)"
                       onclick="APP.Input.onclick(event)"
                       onfocus="APP.Input.onfocus(this)"
                       onpaste="APP.InputIssueKey.onpaste(event)"
                       placeholder="{$_ENV['i18n']('Ticket key')}"
                />
            </label>
        </td>
        <td>
            <textarea data-name="description"
                      onchange="APP.Input.onchange(this)"
                      placeholder="{$_ENV['i18n']('Work description')}"
                      rows="{$_ENV['description_rows']}"
            ></textarea>
        </td>
        <td>
            <label class="input-shell">
                <input type="text"
                       data-name="time_spent"
                       data-value=""
                       maxlength="4"
                       onchange="APP.InputTimeSpent.onchange(this); APP.Input.onchange(this)"
                       onfocus="APP.Input.onfocus(this)"
                       oninput="APP.InputTimeSpent.oninput(event)"
                       onkeydown="APP.InputTimeSpent.onkeydown(event)"
                       onpaste="APP.InputTimeSpent.onpaste(event)"
                       placeholder="H:MM"
                />
            </label>
        </td>
    </tr>

HTML;
?>

<template id="log-template">
<?= $logRowTemplate ?>
</template>

<?php $i = 0; ?>
<?php $freeDays = $_ENV['free_days'] ?? []; ?>
<?php $period = $_ENV['period'] ?? []; ?>
<?php $n = iterator_count($period); ?>

<?php foreach ($period as $dt): ?>
    <?php
    $date1 = $dt->format('Y-m-d');
    $date2 = $dt->format('Y-m-d\T09:00:00.000O');
    /**
     * @see: https://www.php.net/datetime.format
     */
    $dayOfTheWeek = $dt->format('w');
    $isWeekend = ($dayOfTheWeek === '0') || ($dayOfTheWeek === '6');
    $isFreeDay = in_array($date1, array_keys($freeDays));

    $class = 'date' . ($isWeekend ? ' weekend' : ' weekday');
    if ($isFreeDay) {
        $class .= ' free';
    }

    if ($isWeekend && !$_ENV['weekends']) continue;

    $start = new DateTime('00:00');
    $stop = clone $start;
    if (isset($_ENV['timesheet'][$date1]) && is_array($_ENV['timesheet'][$date1])) {
        foreach ($_ENV['timesheet'][$date1] as $k => $v) {
            if ($v['time_spent'] instanceof DateInterval) {
                $start->add($v['time_spent']);
            }
        }
    }
    $totalTimeSpent = $stop->diff($start);

    $fulfilled = ($isFreeDay || $totalTimeSpent->h >= 8) ? 'true' : 'false';
    ?>

    <?php if (($dayOfTheWeek == 1) && ($i > 0)): ?>
    <hr />
    <?php endif; ?>

    <?php if ($isFreeDay): ?>
        <table class="<?= $class ?>">
            <thead>
                <th><?= $_ENV['date_formatters'][0]->format($dt) ?></th>
            </thead>
            <tbody>
                <td style="background: #fff; padding: 10px; text-align: center;"><?= $freeDays[$date1] ?></td>
            </tbody>
        </table>
        <?php continue; ?>
    <?php endif; ?>

    <table class="<?= $class ?>" data-started="<?= $date2 ?>" data-fulfilled="<?= $fulfilled ?>" spellcheck="false">
        <colgroup>
            <col width="35" />
            <col width="35" />
            <col width="40" />
            <col width="70" />
            <col />
            <col width="110" />
        </colgroup>
        <thead>
            <tr>
                <th colspan="3"></th>
                <th colspan="2"><?= $_ENV['date_formatters'][0]->format($dt) ?></th>
                <th data-for="total_time_spent">
                    <span data-name="total_time_spent"><?= $totalTimeSpent->format("%h:%I") ?></span>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php $j = 0; ?>
            <?php if (isset($_ENV['timesheet'][$date1]) && is_array($_ENV['timesheet'][$date1])): ?>
                <?php foreach ($_ENV['timesheet'][$date1] as $k => $v): ?>
                    <tr class="log"
                        data-index="<?= $v['index'] ?>"
                        data-started="<?= $date2 ?>"
                        <?php if ($v['id'] > 0): ?>
                            data-id="<?= $v['id'] ?>"
                            data-synced="<?= $v['synced'] ? 'true' : 'false' ?>"
                        <?php endif; ?>
                    >
                        <td data-for="db_status">
                            <div class="icon icon--db-status"
                                 data-name="db_status"
                            >
                                <svg viewBox="0 0 32 32" width="16" height="16">
                                    <use xlink:href="images/sprite.svg#db-status" />
                                </svg>
                            </div>
                            <div class="icon icon--db-delete tooltip"
                                 data-name="db_delete"
                                 data-title="<?= $_ENV['i18n']('Delete') ?>"
                                 onclick="APP.ButtonDb.onclick(event)"
                                 style="display: none;"
                            >
                                <svg viewBox="0 0 32 32" width="16" height="16">
                                    <use xlink:href="images/sprite.svg#db-delete" />
                                </svg>
                            </div>
                        </td>
                        <td data-for="jira_status">
                            <div class="icon icon--jira-status tooltip"
                                 data-name="jira_status"
                                 data-title="<?= ($v['id'] > 0) ? $_ENV['i18n']('Update') : $_ENV['i18n']('Save') ?>"
                                 onclick="APP.ButtonJira.onclick(event)"
                            >
                                <svg viewBox="0 0 32 32" width="16" height="16">
                                    <use xlink:href="images/sprite.svg#jira-status" />
                                </svg>
                            </div>
                        </td>
                        <td colspan="2">
                            <label class="input-shell">
                                <input type="text"
                                       data-name="issue_key"
                                       data-value="<?= htmlentities($v['issue_key']) ?>"
                                       onblur="APP.Input.onblur(this)"
                                       onclick="APP.Input.onclick(event)"
                                       onfocus="APP.Input.onfocus(this)"
                                    <?php if ($v['id'] !== 0): ?>
                                       aria-label="<?= $_ENV['i18n']('Ticket key') ?>"
                                       readonly
                                    <?php else: ?>
                                       onpaste="APP.InputIssueKey.onpaste(event)"
                                       placeholder="<?= $_ENV['i18n']('Ticket key') ?>"
                                    <?php endif; ?>
                                       value="<?= htmlentities($v['issue_key']) ?>"
                                />
                            </label>
                        </td>
                        <td>
                            <textarea data-name="description"
                                      onchange="APP.Input.onchange(this)"
                                      placeholder="<?= $_ENV['i18n']('Work description') ?>"
                                      rows="<?= $_ENV['description_rows'] ?>"
                            ><?= htmlentities($v['description']) ?></textarea>
                        </td>
                        <td>
                            <label class="input-shell">
                                <input type="text"
                                       data-name="time_spent"
                                       data-value="<?= $v['time_spent_formatted'] ?>"
                                       maxlength="4"
                                       onchange="APP.InputTimeSpent.onchange(this); APP.Input.onchange(this)"
                                       onclick="APP.Input.onclick(event)"
                                       onfocus="APP.Input.onfocus(this)"
                                       oninput="APP.InputTimeSpent.oninput(event)"
                                       onkeydown="APP.InputTimeSpent.onkeydown(event)"
                                       onpaste="APP.InputTimeSpent.onpaste(event)"
                                       placeholder="H:MM"
                                       value="<?= $v['time_spent_formatted'] ?>"
                                />
                            </label>
                        </td>
                    </tr>
                    <?php $j++; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php $nEmptyRowsLimit = 1; ?>
            <?php $nEmptyRows = ($j < $nEmptyRowsLimit) ? ($nEmptyRowsLimit - $j) : 1; ?>

            <?php while ($nEmptyRows > 0): ?>
                <?= str_replace('data-started=""', 'data-started="' . $date2 . '"', $logRowTemplate) ?>
                <?php $nEmptyRows--; ?>
            <?php endwhile; ?>

        </tbody>
    </table>
    <?php $i++; ?>
<?php endforeach; ?>
