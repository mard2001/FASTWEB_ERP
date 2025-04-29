<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Receiving Report</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <style>
        /* Header (Company Name & Logo) */
        .rr-header {
            font-size: 1.3rem;
            /* Adjust between 14–18pt */
            font-weight: bold;
            text-transform: uppercase;
            font-family: Arial, Helvetica, sans-serif;
        }

        .headerDetails{
            padding-bottom: 10px; 
            border-bottom: 2px solid #000 !important;
        }

        .tablefooterDiv{
            border-top: 2px solid #000 !important;
        }

        .commentDiv{
            border: 1.5px solid #2e2e2e !important;
            min-height: 50px; 
            max-width:500px;
            font-size: 10px;
        }
        .rr-info * {
            font-size: 10px;
            border: none;
            table-layout: fixed; /* Ensures the columns respect assigned widths */
            width: 100%; /* Makes table responsive */
            height: 30px !important; /* Ensure at least 2 lines */
            white-space: normal;
            word-wrap: break-word;
            overflow: hidden;
            line-height: 10px;
        }

        .rr-info tbody tr td{
            padding: .15rem .25rem;
        }

        .rr-info thead tr:first-child {
            text-align: center;
            text-decoration: underline;
            padding: .15rem .25rem;
            height: 20px !important;
        }

        .signatory{
            border-top: 2px solid #000 !important;
            padding-top: 10px;
        }

        .signatory div div:last-child{
            margin-top: 30px;
        }

        .footerText {
            width: 100%;
            text-align: center;
            background: white;
            padding: 10px 0;
            font-size: 12px;
            display: flex;
            flex-direction: column;
        }

        

        @media print {
            @page {
                margin: 0; /* Removes default browser print margins */
            }
            body {
                margin: 0;
                padding: 0 20px;
            }

            .header, .table-container {
                page-break-before: always;
            }
            hr {
                border: 10px solid black !important;
            }

            .footerTextLast {
                /* position: absolute; */
                bottom: 0;
                left: 0;
                width: 100%;
                text-align: center;
                background: white;
                padding: 10px 0;
                font-size: 12px;
            }
        }
    </style>

    <body>
        <?php
            $maxRowsPerPage = 15;  
            $rowCount = 0; 
            $totalPages = ceil(count($report->rrdetails)/$maxRowsPerPage);
            $pageNumber = 1;
            $totalAmount = 0;
        ?>

        <?php $__currentLoopData = $report->rrdetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if($rowCount % $maxRowsPerPage == 0): ?>
                <?php if($rowCount > 0): ?>
                    <div style="page-break-before: always;"></div>
                    <?php
                        $pageNumber++;
                    ?>
                <?php endif; ?>

                <header class="px-2 py-1">
                    <div class="d-flex flex-row mb-3">
                        <div class="p-2 pt-3">
                            <img src="https://jobslin.com/storage/logow/ph/FAST/fast-unimerchants-inc-1722319497.webp" alt="Description" width="250" height="80">
                        </div>
                        <div class="p-2">
                            <p class="m-0" style="font-size: 16px; font-weight:700">FAST DISTRIBUTION CORPORATION</p>
                            <p class="m-0" style="font-size: 10px;">H Abellana Street, Canduman, Mandaue City, Cebu, 6014</p>
                            <p class="m-0" style="font-size: 10px;">Tel. No. (032) 343-7888</p>
                            <p class="m-0" style="font-size: 10px;">Business Style: Wholesale and Retail Distribution Services</p>
                            <p class="m-0" style="font-size: 10px;"> VAT REG. TIN 485-010-749-00006</p>
                            
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p class="text-nowrap text-center rr-header my-0" style="font-size: 16px;">Receiving Report</p>
                    </div>
                
                    <div class="d-flex justify-content-between headerDetails">
                        <div>
                            <p class="distName fw-bold mb-4" style="font-size: 14px;"><?php echo e($report->distName); ?></p>
                            <div class="d-flex justify-content-between" style="min-width: 500px">
                                <p class="mb-0" style="font-size: 12px;">Supplier Code:<strong class="supplierCode text-uppercase" style="margin-left: 19px"><?php echo e($report->poincluded->SupplierCode); ?></strong></p>
                                <p class="mb-0" style="margin-right: 20px; font-size: 12px;">Supplier's TIN:<strong class="supplierCode text-uppercase" style="margin-left: 15px"></strong></p>
                            </div>
                            <p class="mb-0" style="font-size: 12px;">Supplier Name:<strong class="supplierName text-uppercase" style="margin-left: 15px"><?php echo e($report->poincluded->posupplier->SupplierName); ?></strong></p>
                            <p class="mb-0" style="font-size: 12px;">Address:<span class="supplierAdd text-uppercase" style="margin-left: 50px"><?php echo e($report->poincluded->posupplier->CompleteAddress); ?></span></p>
                        </div>
                        <div>
                            <p class="mb-0" style="font-size: 12px;">RR No.:<strong class="rrNum text-uppercase" style="margin-left: 31px"><?php echo e($report->RRNo); ?></strong></p>
                            <p class="mb-0" style="font-size: 12px;">Date:<span class="rrDate" style="margin-left: 44px"><?php echo e($report->RRDATE); ?></span></p>
                            <p class="" style="font-size: 12px;">Reference:<strong class="rrRefNum" style="margin-left: 16px"><?php echo e($report->Reference); ?></strong></p>
                            <p class="mb-0" style="font-size: 12px;">Status:<span class="rrNum text-uppercase" style="margin-left: 37px"><?php echo e($report->Status == 1 ? "Active":"Deleted"); ?></span></p>
                            <p class="mb-0" style="font-size: 12px;"><strong class="rrNum text-uppercase" style="margin-left: 72px"><?php echo e($report->rrStat2); ?></strong></p>
                        </div>
                    </div>
                </header>

                <table class="table rr-info">
                    <thead>
                        <tr>
                            <th style="width: 5%;">No.</th>
                            <th style="width: 10%;">Item</th>
                            <th class="text-start" style="width: 25%;">Description</th>
                            <th style="width: 5%;">Quantity</th>
                            <th style="width: 5%;">OuM</th>
                            <th style="width: 10%;">WhsCode</th>
                            <th class="text-end" style="width: 10%;">UnitPrice</th>
                            <th class="text-end" style="width: 10%;">Net of Vat</th>
                            <th class="text-end" style="width: 10%;">Vat</th>
                            <th class="text-end" style="width: 10%;">Gross</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php endif; ?>

            <tr>
                <td class="text-center" style="width: 5%;"><?php echo e($index + 1); ?></td>  
                <td class="text-center" style="width: 10%;"><?php echo e($item->SKU); ?></td>  
                <td class="text-start" style="width: 25%;"><?php echo e($item->product->Description); ?></td>  
                <td class="text-start" style="width: 5%;"><?php echo e(number_format($item->convertedQuantity['convertedToLargestUnit'],2)); ?></td>  
                <td class="text-center" style="width: 5%;"><?php echo e($item->convertedQuantity['uom']); ?></td>  
                <td class="text-center" style="width: 10%;"><?php echo e($item->itemWhsCode); ?></td>  
                <td class="text-end" style="width: 10%;"><?php echo e($item->UnitPrice); ?></td>  
                <td class="text-end" style="width: 10%;"><?php echo e($item->NetVat); ?></td>  
                <td class="text-end" style="width: 10%;"><?php echo e($item->Vat); ?></td>  
                <td class="text-end" style="width: 10%;"><?php echo e($item->Gross); ?></td>  
            </tr>
            <?php
                $totalAmount += floatval($item->Gross);
                $rowCount++; // Increment row count
            ?>

            <?php if($rowCount % $maxRowsPerPage == 0): ?>
                    </tbody>
                </table>
                <span style="font-size: 9px;">*Report continues on the next page...</span>
                <?php if($pageNumber != $totalPages ): ?>
                    <div class="footerText" style="margin-top: 180px">
                        <strong style="font-size: 9px">"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX."</strong>
                        <span style="font-size: 8px">'THIS IS A SYSTEM-GENERATED DOCUMENT.'</span>
                        <?php
                            echo "<div style='text-align: center; font-size: 7px'>Page {$pageNumber} of {$totalPages}</div>";
                        ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if($rowCount % $maxRowsPerPage != 0): ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="d-flex justify-content-end tablefooterDiv">
            <div class="totalDiv">
                <table>
                    <tbody>
                        <tr>
                            <th>Total</th>
                            <td style="padding-left:20px; border-bottom: 5px solid; border-style: double;"><?php echo e(number_format($totalAmount, 2)); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if($pageNumber == $totalPages): ?>
            <div class="d-flex justify-content-start">
                <div>
                    <span style="font-size: 12px">Comments:</span>
                    <div class="commentDiv px-3 mx-1 mb-4" >
                        THIS IS A TESTING COMMENT. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry
                    </div>
                </div>
            </div>
            <?php
                $remainingRows = ($maxRowsPerPage*$pageNumber)-$rowCount; 
                $maxMargin = $remainingRows*28;
                echo '<div style="height: '.$maxMargin.'px !important"></div>';
            ?>
            <div class="d-flex justify-content-between signatory text-center mb-3" style="font-size: 12px;">
                <div class="preparedDiv">
                    <div>Prepared by:</div>
                    <div class="preparer "><?php echo e($report->PreparedBy); ?></div>
                </div>
                <div class="checkedDiv">
                    <div>Checked by:</div>
                    <div class="checker "><?php echo e($report->PrintedBy== '' ? "Supervisor": $report->PrintedBy); ?></div>
                </div>
                <div class="approvedDiv">
                    <div>Approved by:</div>
                    <div class="approver "><?php echo e($report->CheckedBy == '' ? "Manager" : $report->CheckedBy); ?></div>
                </div>
            </div>

            <div class="footerText" style="margin-top: 20px">
                <strong style="font-size: 9px">"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX."</strong>
                <span style="font-size: 8px">'THIS IS A SYSTEM-GENERATED DOCUMENT.'</span>
                <?php
                    echo "<div style='text-align: center; font-size: 7px'>Page {$pageNumber} of {$totalPages}</div>";
                ?>
            </div>
        <?php endif; ?>   
    </body>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    


    <script>
        window.onload = function() {
            window.print();

            // Close the tab after printing or if the user cancels
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</html><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\backupFASTERP_2025-03-17\resources\views/Pages/Printing/RR_printing.blade.php ENDPATH**/ ?>