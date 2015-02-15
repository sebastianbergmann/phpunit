<html>
    <head>
        <STYLE type="text/css">

            body, td {
                background-color: lightgrey;
            }

            table.outline, outlineFailure {
                background-color: black;
                border-width: 1px;
            }

            td {
                padding: 2px;
            }

            th {
                text-align: left;
                color: white;
                background-color: black;
            }

            .success {
                background-color: lightgreen;
            }

            .failure {
                background-color: orange;
            }
            .info {
                padding: 2px;
                color: orange;
            }

        </STYLE>
    </head>
    <body>
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="optionsForm">
            <table align="center" class="outline" width="70%">
                <tr>
                    <th colspan="10">
                        Options
                    </th>
                </tr>
                <tr>
                    <td colspan="10">
                        <input type="checkbox" onClick="unCheckAll()" name="allChecked">
                        (un)check all
                        &nbsp; &nbsp;
                        show OK <input type="checkbox" name="showOK" <?php echo @$request['showOK']?'checked':''?>>
                        &nbsp; &nbsp;
                        <input type="submit" name="submitted" value="run tests">
                    </td>
                </tr>

                <?php foreach($suiteResults as $aResult): ?>
                    <tr>
                        <th colspan="10">
                            <input type="checkbox" name="<?php echo $aResult['name'] ?>" <?php echo @$request[$aResult['name']]?'checked':'' ?>>
                            <?php echo $aResult['name'] ?>
                            &nbsp;
                            <?php if (isset($aResult['addInfo'])): ?>
                                <font class="info"><?php echo @$aResult['addInfo'] ?></font>
                            <?php endif ?>
                        </th>
                    </tr>

                    <?php if(@$aResult['percent']): ?>
                        <tr>
                            <td colspan="10" nowrap="nowrap">
                                <table style="width:100%; padding:2px;" cellspacing="0" cellspan="0" cellpadding="0">
                                    <tr>
                                        <td width="<?php echo $aResult['percent'] ?>%" class="success" align="center" style="padding:0;">
                                            <?php echo $aResult['percent']?$aResult['percent'].'%':'' ?>
                                        </td>
                                        <td width="<?php echo 100-$aResult['percent'] ?>%" class="failure" align="center" style="padding:0;">
                                            <?php echo (100-$aResult['percent'])?(100-$aResult['percent'].'%'):'' ?>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if(@$aResult['counts']): ?>
                        <tr>
                            <td colspan="10">
                                <?php foreach($aResult['counts'] as $aCount=>$value): ?>
                                    <?php echo $aCount ?>s = <?php echo $value ?> &nbsp; &nbsp; &nbsp; &nbsp;
                                <?php endforeach ?>
                            </td>
                        </tr>
                    <?php endif ?>

                    <?php if(isset($aResult['results']['failures']) && sizeof($aResult['results']['failures']))
                        foreach($aResult['results']['failures'] as $aFailure): ?>
                        <tr>
                            <td class="failure"><?php echo $aFailure['testName'] ?></td>
                            <td class="failure">
                                <?php if(isset($aFailure['message']) && $aFailure['message']): ?>
                                    <?php echo $aFailure['message'] ?>
                                <?php else: ?>
                                    <table class="outlineFailure">
                                        <tr>
                                            <td>expected</td>
                                            <td><?php echo $aFailure['expected'] ?></td>
                                        </tr>
                                        <tr>
                                            <td>actual</td>
                                            <td><?php echo $aFailure['actual'] ?></td>
                                        </tr>
                                    </table>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    <?php if(isset($aResult['results']['errors']) && sizeof($aResult['results']['errors']))
                        foreach($aResult['results']['errors'] as $aError): ?>
                        <tr>
                            <td class="failure"><?php echo $aError['testName'] ?></td>
                            <td class="failure">
                                <?php echo $aError['message'] ?>
                            </td>
                        </tr>
                    <?php endforeach ?>

                    <?php if(isset($aResult['results']['passed']) && sizeof($aResult['results']['passed']))
                        foreach($aResult['results']['passed'] as $aPassed): ?>
                        <tr>
                            <td class="success"><?php echo $aPassed['testName'] ?></td>
                            <td class="success"><b>OK</b></td>
                        </tr>
                    <?php endforeach ?>

                <?php endforeach ?>
            </table>
        </form>

        <script>
            var allSuiteNames = new Array();
            <?php foreach($suiteResults as $aResult): ?>
                allSuiteNames[allSuiteNames.length] = "<?php echo $aResult['name'] ?>";
            <?php endforeach ?>
            function unCheckAll()
            {
                _checked = document.optionsForm.allChecked.checked;
                for (i=0;i<allSuiteNames.length;i++) {
                    document.optionsForm[allSuiteNames[i]].checked = _checked;
                }
            }
        </script>

    </body>
</html>
