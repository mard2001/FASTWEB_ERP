@extends('Layout.layout')

@section('html_title')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<title>Uploader Page</title>
@endsection

@section('title_header')
<x-header title="Uploader Page" />
@endsection

@section('table')
<style>
    .secBtns .selected {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
    }

    .secBtns button {
        border-bottom: 2px solid transparent;
        border-top: 1px solid transparent;
        border-left: 1px solid transparent;
        border-right: 1px solid transparent;
    }

    .secBtns button:hover {
        background-color: rgba(23, 162, 184, 0.10);
        border-bottom: 2px solid #0275d8;
        border-top: 0.5px solid #0275d8;
        border-left: 0.5px solid #0275d8;
        border-right: 0.5px solid #0275d8;
    }

    .autocompleteHover:hover {
        background-color: #3B71CA;
        cursor: pointer;
    }

    .ui-autocomplete {
        z-index: 9999 !important;
    }

    .fs15 * {
        font-size: 15px;
    }
</style>

<x-table>
    <x-slot:td>
        <td class="col">PeriodYear</td>
        <td class="col">PeriodMonth</td>
        <td class="col">BusinessUnit</td>
        <td class="col">PAType</td>
        <td class="col">CustomerClass</td>
        <td class="col">StockCode</td>
        <td class="col">DropSize</td>
        <td class="col">Points</td>
        <td class="col">Amount</td>
        <td class="col">BonusPoint</td>
        <td class="col">MHCount</td>
        <td class="col">UpdatedBy</td>
        <td class="col">DateUpdated</td>
    </x-slot:td>
</x-table>


@endsection

@section('modal')
<x-form_modal>
    <x-slot:form_fields>
        <div class="row h-100 fs15">
            <div class="col mt-1 flex-wrap">

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="PeriodYear">Period Year</label>
                            <input disabled type="number" id="PeriodYear" name="PeriodYear" class="form-control bg-white"
                                required placeholder="Period Year">
                        </div>

                        <div class="col">
                            <label for="PeriodMonth">Period Month</label>
                            <input disabled type="number" id="PeriodMonth" name="PeriodMonth" class="form-control bg-white"
                                required placeholder="Period Month">
                        </div>
                    </div>
                </div>


                <div class="col mt-2">
                    <label for="BusinessUnit">Business Unit</label>
                    <input disabled type="text" id="BusinessUnit" name="BusinessUnit" class="form-control bg-white"
                        required placeholder="Business Unit">
                </div>

                <div class="col mt-2">
                    <label for="PAType">PA Type</label>
                    <input disabled type="text" id="PAType" name="PAType" class="form-control bg-white"
                        required placeholder="PA Type">
                </div>

                <div class="col mt-2">
                    <label for="CustomerClass">Customer Class</label>
                    <input disabled type="number" id="CustomerClass" name="CustomerClass" class="form-control bg-white"
                        required placeholder="Customer Class">
                </div>
            </div>

            <div class="col mt-1">

                <div class="col mt-2">
                    <label for="StockCode">Stock Code</label>
                    <input disabled type="number" id="StockCode" name="StockCode" class="form-control bg-white"
                        required placeholder="Stock Code">
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="DropSize">Drop Size</label>
                            <input disabled type="number" id="DropSize" name="DropSize" class="form-control bg-white"
                                required placeholder="Drop Size">
                        </div>

                        <div class="col">
                            <label for="Points">Points</label>
                            <input disabled type="number" id="Points" name="Points" class="form-control bg-white"
                                required placeholder="Points">
                        </div>
                    </div>
                </div>

                <div class="col mt-2">
                    <div class="row">
                        <div class="col">
                            <label for="BonusPoint">Bonus Point</label>
                            <input disabled type="number" id="BonusPoint" name="BonusPoint" class="form-control bg-white"
                                required placeholder="Bonus Point">
                        </div>

                        <div class="col">
                            <label for="MHCount">MHCount</label>
                            <input disabled type="number" id="MHCount" name="MHCount" class="form-control bg-white"
                                required placeholder="MHCount">
                        </div>

                    </div>
                </div>

                <div class="col mt-2">
                    <label for="Amount">Amount</label>
                    <input disabled type="number" id="Amount" name="Amount" class="form-control bg-white"
                        required placeholder="Amount">
                </div>


            </div>
        </div>
    </x-slot:form_fields>
</x-form_modal>
@endsection

@section('pagejs')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script> -->

<script src="{{ asset('assets/js/maintenance_uploader/pamasterslist.js') }}"></script>
<script>
    $(document).ready(async function() {
        $("#uploadBtn").off("click", modalUploader);


        //modify the function
        modalUploader = async function() {

            // console.log('test click'); return;
            var file_data = $('#formFileMultiple').prop('files');

            var pdfOnly = true;
            for (var i = 0; i < file_data.length; i++) {
                if (!file_data[i].name.split('.').pop() == 'pdf' || !file_data[i].name.split('.').pop() == 'PDF') {
                    pdfOnly = false;
                    break;
                }
            }

            if (pdfOnly && file_data.length > 0) {
                for (var i = 0; i < file_data.length; i++) {
                    var fileFormData = new FormData();
                    fileFormData.append('pdf_file', file_data[i]);

                    var convertedtoString = await readpdf(file_data[i]);
                    // console.log(convertedtoString);
                    // HeaderFromShellPO(convertedtoString);
                    // var extractedData = HeaderFromFUIRR(convertedtoString);
                    // fileFormData.append('data', JSON.stringify(extractedData));

                    var retrievedUser = JSON.parse(localStorage.getItem('dbcon'));

                    fileFormData.append('conn', JSON.stringify(retrievedUser));
                    fileFormData.append('extractedString', convertedtoString);

                    // console.log(JSON.stringify(extractedData, null, 2));

                    // var data = {
                    //     convertedtoString: convertedtoString
                    // };

                    ajaxCall(1, fileFormData);

                }

            } else {
                alert('Empty File Please Select a PDF File');
            }

        };

        $("#uploadBtn").click(modalUploader);

        async function readpdf(file) {

            try {
                const fileReader = new FileReader();

                const typedArray = await new Promise((resolve, reject) => {
                    fileReader.onload = () => resolve(new Uint8Array(fileReader.result));
                    fileReader.onerror = reject;
                    fileReader.readAsArrayBuffer(file);
                });

                const pdf = await pdfjsLib.getDocument(typedArray).promise;
                let pdfText = '';

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const textContent = await page.getTextContent();

                    textContent.items.forEach(textItem => {
                        if (textItem.str.trim()) { // Skip empty strings
                            pdfText += textItem.str + '\n';
                        }
                    });
                }

                return pdfText;
            } catch (error) {
                console.error('Error occurred:', error);
                return '';
            }

        }

        async function ajaxCall(method, formData = null) {
            switch (method) {
                case 1: // BULK INSERT
                    apiMethod = 'POST';

                    break;
                case 2: // UPDATE DATA
                    apiMethod = 'POST';

                    break;
                case 3: // DELETE DATA
                    apiMethod = 'POST';

                    break;
                case 4: // GET SINGLE DATA VIA ID
                    apiMethod = 'GET';

                    break;
                case 5: // GET ALL DATA
                    apiMethod = 'GET';

                    break;
                case 6: // INSERT DATA
                    apiMethod = 'POST';

                    break;
            }


            return await $.ajax({
                url: globalApi + 'api/upload-po-pdf',
                type: apiMethod,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token')
                },
                processData: false, // Required for FormData
                contentType: false, // Required for FormData
                data: formData, // Convert the data to JSON format
                // data: xmlJson, // Convert the data to JSON format

                success: async function(response) {
                    console.log(response);

                    if (response.status_response != 1) {

                        //console.log(JSON.stringify(response, null, 2));
                        //console.log(response.extracted_text);



                    }

                    //console.log(response);
                    return response;

                },
                error: async function(xhr, status, error) {


                    // Swal.fire({
                    //     icon: "error",
                    //     title: "Api Error",
                    //     text: JSON.stringify({ xhr, status, error }, null, 2),
                    // });

                    console.log(xhr, status, error)

                    return xhr, status, error;
                }
            });
        }


        async function loadPdfText(pdfUrl) {
            try {
                const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
                let pdfText = '';

                for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                    const page = await pdf.getPage(pageNumber);
                    const textContent = await page.getTextContent();

                    textContent.items.forEach(textItem => {
                        if (textItem.str.charCodeAt(0) !== 32) {
                            pdfText += textItem.str + '\n';
                        }
                    });
                }

                //console.log(pdfText);
                return pdfText;
            } catch (error) {
                console.error('Error occurred:', error);
                return false;
            }
        }



        let businessEntitySuffixes = [
            "Inc.", "Incorporated", // Incorporated
            "Corp.", "Corporation", // Corporation
            "Ltd.", "Limited", // Limited
            "LLC", "Limited Liability Company", // Limited Liability Company
            "LLP", "Limited Liability Partnership", // Limited Liability Partnership
            "Co.", "Company", // Company
            "PLC", "Public Limited Company", // Public Limited Company
            "GmbH", "Gesellschaft mit beschränkter Haftung", // Limited Liability Company (German-speaking countries)
            "S.A.", "Société Anonyme", // Public Limited Company (French-speaking countries)
            "S.A.S.", "Société par Actions Simplifiée", // Simplified Joint-stock Company (French-speaking countries)
            "Pty Ltd", "Proprietary Limited", // Proprietary Limited Company (Australia, South Africa)
            "B.V.", "Besloten Vennootschap", // Private Limited Company (Dutch-speaking countries)
            "S.r.l.", "Società a Responsabilità Limitata", // Limited Liability Company (Italian-speaking countries)
            "FZC", "Free Zone Company", // Free Zone Company (UAE)
            "K.K.", "Kabushiki Kaisha", // Joint-stock Company (Japan)
            "N.V.", "Naamloze Vennootschap", // Public Company (Dutch-speaking countries)
            "A.G.", "Aktiengesellschaft", // Joint-stock Company (German-speaking countries)
            "M.A.", "Merchant Association", // Merchant Association
            "BHD", "Berhad", // Public Limited Company (Malaysia)
            "Sdn. Bhd.", "Sendirian Berhad", // Private Limited Company (Malaysia)
            "C.V.", "Commanditaire Vennootschap", // Limited Partnership (Dutch-speaking countries)
            "S.p.A.", "Società per Azioni", // Joint-stock Company (Italian-speaking countries)
            "P.C.", "Professional Corporation", // Professional Corporation
            "L.L.C.", "Limited Liability Corporation", // Limited Liability Corporation
            "LTD", "Limited", // Limited
            "SCS", "Société en Commandite Simple", // Limited Partnership (French-speaking countries)
            "SICAV", "Société d'Investissement à Capital Variable", // Investment Company with Variable Capital (French-speaking countries)
            "FLLC", "Foreign Limited Liability Company", // Foreign Limited Liability Company
            "T.L.C.", "Trading Limited Corporation", // Trading Limited Corporation
            "C.C.", "Close Corporation", // Close Corporation
            "N.V.O.C.", "Naamloze Vennootschap Openbare Compagnie", // Public Company (Dutch-speaking countries)
            "GIE", "Groupement d’Intérêt Économique", // Economic Interest Group (French-speaking countries)
            "A.E.", "Anonimos Etairia", // Limited Liability Company (Greece)
            "E.I.R.L.", "Entreprise Individuelle à Responsabilité Limitée", // Sole Proprietorship with Limited Liability (French-speaking countries)
            "D.O.O.", "Društvo sa Ograničenom Odgovornošću", // Limited Liability Company (Serbo-Croatian-speaking countries)
            "Kft.", "Korlátolt Felelősségű Társaság", // Limited Liability Company (Hungary)
            "L.P.", "Limited Partnership", // Limited Partnership
            "R.L.", "Responsabilidad Limitada", // Limited Liability (Spanish-speaking countries)
            "C.L.", "Closed Corporation", // Closed Corporation
            "M.B.H.", "Mittlerer Gesellschaftsbereich", // Middle Business Area
            "S.L.", "Sociedad Limitada", // Limited Liability Company (Spanish-speaking countries)
            "S.A.R.L.", "Société à Responsabilité Limitée", // Limited Liability Company (French-speaking countries)
            "OÜ", "Osaühing", // Private Limited Company (Estonia)
            "P.S.C.", "Public Service Corporation", // Public Service Corporation
            "UAB", "Uždaroji Akcinė Bendrovė", // Private Limited Company (Lithuania)
            "JSC", "Joint-Stock Company", // Joint-stock Company
            "A.B.", "Akcinė Bendrovė", // Joint-stock Company (Lithuania)
            "FPI", "Financial Professional Institution", // Financial Professional Institution
            "Zrt.", "Zártkörűen Működő Részvénytársaság", // Closed Joint-stock Company (Hungary)
            "SICAF", "Società di Investimento a Capitale Fisso", // Fixed Capital Investment Company (Italy)
            "SIA", "Sabiedrība ar ierobežotu atbildību", // Limited Liability Company (Latvia)
            "LLLP", "Limited Liability Limited Partnership" // Limited Liability Limited Partnership
        ];

        //tansform convert all to lowercase for easy comparison
        businessEntitySuffixes = businessEntitySuffixes.map(item => item.toLowerCase());



        function validateExtractedData(items) {

            items.forEach(item => {



            });

        }



    });
</script>

@endsection