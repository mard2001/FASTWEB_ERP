<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purhcase Order</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    /* Header (Company Name & Logo) */
    .po-header {
        font-size: 1.8rem;
        /* Adjust between 14–18pt */
        font-weight: bold;
        text-transform: uppercase;
        font-family: Arial, Helvetica, sans-serif;
    }


    /* Purchase Order Title (e.g., "PURCHASE ORDER") */
    .po-title {
        font-size: 18pt;
        /* Adjust between 16–20pt */
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
    }

    /* Section Headings (e.g., "Vendor Details", "Ship To", "PO Number") */
    .po-section-heading {
        font-size: 12px;
        /* Adjust between 10–12pt */
        font-weight: bold;
    }

    .po-info * {
        font-size: 13px;
        /* Adjust between 10–12pt */
    }

    .po-text * {
        font-size: 12px;

    }

    .bg-tr {
        background-color: #33336F !important;
    }
</style>

<header class="px-2 py-1">
    <div class="d-flex justify-content-between">
        <p style="font-size: 18px; font-weight: 600">FAST DISTRIBUTION CORP.</p>
        <p class="text-nowrap text-primary text-center po-header my-0">PURCHASE ORDER</p>
    </div>

    <div class="d-flex justify-content-between po-info">
        <div>
            <p class="m-0">H Abellana Street, Canduman</p>
            <p class="m-0">Mandaue City, Cebu, 6014</p>
            <p class="m-0">Phone: (000) 000-0000</p>
        </div>
        <div class="d-flex">
            <div>
                <p class="m-0">PO Date: </p>
                <p class="m-0">PO Number:</p>

            </div>
            <div class="px-2">
                <p class="m-0"> <?php echo e($po->PODate); ?> </p>
                <p class="m-0"> <?php echo e($po->PONumber); ?> </p>
            </div>
        </div>
    </div>
</header>


<body>
    <div class="d-flex justify-content-between flex-wrap my-2">
        <div class="my-sm-2 bg-info bg-opacity-25 po-info" style="width: 40%;">
            <div class="bg-tr py-1 px-2 d-flex align-items-center text-white" style="font-size: 14px; font-weight: 600">
                VENDOR
            </div>

            <div class="bg-info py-1 px-2 d-flex align-items-center bg-opacity-50">
                <?php echo e($po->posupplier['SupplierName']); ?>

            </div>

            <div class="d-flex px-2">
                <span>Contact: </span>
                <p class="mx-2 my-0"> <?php echo e($po->posupplier['ContactPerson']); ?>

                </p>
            </div>

            <div class="px-2">
                <p class="text-wrap m-0 pe-3">
                    <?php echo e($po->posupplier['CompleteAddress']); ?>

                </p>

                <div>
                    <?php echo e($po->posupplier['ContactNo']); ?>

                </div>
            </div>
        </div>

        <div class="my-sm-2 bg-info bg-opacity-25 po-info" style="width: 40%;">
            <div class="bg-tr py-1 px-2 d-flex align-items-center text-white" style="font-size: 14px; font-weight: 600">
                SHIP TO
            </div>

            <div class="bg-info py-1 px-2 d-flex align-items-center bg-opacity-50">
                Info
            </div>

            <div class="d-flex px-2">
                <span>Contact: </span>
                <p class="mx-2 my-0"> <?php echo e($po->contactPerson); ?>

                </p>
            </div>

            <div class="px-2">
                <p class="text-wrap m-0 pe-3">
                    <?php echo e($po->deliveryAddress); ?>

                </p>

                <div>
                    Phone: <?php echo e($po->contactNumber); ?>

                </div>
            </div>


        </div>

    </div>

    <table class="table table-bordered my-2 po-info">
        <tbody>
            <tr>
                <th class="col-3 p-1 bg-tr text-white fw-semibold">REQUISITIONER</th>
                <th class="col-3 p-1 bg-tr text-white fw-semibold">SHIP VIA</th>
                <th class="col-3 p-1 bg-tr text-white fw-semibold">F.O.B.</th>
                <th class="col-3 p-1 bg-tr text-white fw-semibold">SHIPPING TERMS</th>
            </tr>
            <tr>
                <td class="col p-1"><?php echo e($po->EncoderID); ?></td>
                <td class="col p-1">Road Delivery</td>
                <td class="col p-1"><?php echo e($po->FOB); ?></td>
                <td class="col p-1"> <?php echo e($po->posupplier['TermsCode']); ?>

                </td>
            </tr>
        </tbody>
    </table>

    <table class="table table-bordered po-info">
        <thead>
            <tr>
                <td class="col bg-tr p-1 text-white fw-semibold">SKU</td>
                <td class="col-5 bg-tr p-1 text-white fw-semibold">Description</td>
                <td class="col-1 bg-tr p-1 text-white fw-semibold text-center">UOM</td>
                <td class="col bg-tr p-1 text-white fw-semibold text-end">Quantity</td>
                <td class="col bg-tr p-1 text-white fw-semibold text-end">Unit Price</td>
                <td class="col bg-tr p-1 text-white fw-semibold text-end">Total</td>
            </tr>
        </thead>
        <tbody class="border-dark">
            <?php $__currentLoopData = $po->POItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td class="col py-1"><?php echo e($item['StockCode']); ?></td>
                <td class="col py-1"><?php echo e($item['Decription']); ?></td>
                <td class="col-1 py-1 text-center"> <?php echo e($item['UOM']); ?></td>
                <td class="col py-1 text-end"><?php echo e(number_format($item['Quantity'], 1)); ?></td>
                <td class="col py-1 text-end"> <?php echo e(number_format($item['PricePerUnit'], 2)); ?></td>
                <td class="col py-1 text-end"> <?php echo e(number_format($item['TotalPrice'], 2)); ?></td>

            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="col bg-info bg-opacity-25">Comments or Special Instructions</td>
                <td class="col py-1">SUBTOTAL</td>
                <td class="col py-1 text-end"> <?php echo e(number_format($po->subTotal - ($po->subTotal * .12), 2)); ?></td>
            </tr>

            <tr>
                <td rowspan="3" colspan="4" class="col">
                    <p><?php echo e($po->SpecialInstruction); ?></p>
                </td>
                <td class="col py-1">TAX</td>
                <td class="col py-1 text-end"> <?php echo e(number_format($po->subTotal * .12, 2)); ?></td>


            </tr>

            <tr>
                <td class="col py-1">TOTAL ITEM</td>
                <td class="col py-1 text-end"> <?php echo e(count($po->POItems)); ?></td>

            </tr>

            <tr>
                <td class="col py-1">TOTAL</td>
                <td class="col py-1 text-end"> <?php echo e(number_format($po->totalCost, 2)); ?></td>
            </tr>
        </tfoot>
    </table>
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.onload = function() {
        window.print();

        // Close the tab after printing or if the user cancels
        window.onafterprint = function() {
            window.close();
        };
    };
</script>


<script>
    async function savePageAsPDF() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4'); // Portrait, millimeters, A4 size

        // Capture the entire body as an image
        const canvas = await html2canvas(document.body, {
            scale: 2
        });
        const imgData = canvas.toDataURL("image/png");

        // Calculate dimensions to fit the A4 page
        const imgWidth = 210; // A4 width in mm
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio

        doc.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
        doc.save("full-page.pdf");
    }

    // Call the function when Ctrl + P is pressed
    document.addEventListener("keydown", function(event) {
        if (event.ctrlKey && event.key === "p") {
            event.preventDefault(); // Prevent default print dialog
            savePageAsPDF();
        }
    });
</script>

</html><?php /**PATH C:\xampp\htdocs\bckup_FASTERP\FASTERP_2025-03-17\resources\views/Pages/PurchaseOrder-PDF.blade.php ENDPATH**/ ?>