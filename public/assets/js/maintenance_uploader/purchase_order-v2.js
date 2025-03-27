var MainTH, selectedMain;
var ItemsTH, selectedItem;
var ajaxMainData, ajaxItemsData;
var shippedToData, selecteddShippedTo;
var vendordata, selectedVendor;
var productConFact;
var itemTmpSave = [];
var priceCodes;

const dataTableCustomBtn = `<div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2" style="font-size: 12px;">
<div class="btn d-flex justify-content-around px-2 align-items-center me-1" id="addBtn">
    <div class="btnImg me-2" id="addImg">
    </div>
    <span>Add new</span>
</div>

<div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvShowBtn">
    <div class="btnImg me-2" id="dlImg">
    </div>
    <span>Download Template</span>
</div>

<div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvUploadShowBtn">
    <div class="btnImg me-2" id="ulImg">
    </div>
    <span>Upload Template</span>
</div>
</div>`;

$(document).ready(async function () {
  await datatables.loadPO();
  await initVS.liteDataVS();
  await initVS.bigDataVS();
  await getProductPriceCodes();
  POItemsModal.setValidator();

  $("#POHeaderTable").on("click", "tbody tr", async function () {
    // selectedMain = ajaxMainData.find(item => item.id == $(this).attr('id'));
    const selectedPO = $(this).attr("id");

    await ajax(
      "api/orders/po/" + selectedPO,
      "GET",
      null,
      (response) => {
        // Success callback

        if (response.success == 1) {
          POModal.viewMode(response.data);
          selectedMain = response.data;
          selectedVendor = vendordata.find(
            (item) => item.SupplierName == selectedMain.SupplierName
          );
        } else {
          Swal.fire({
            title: "Opppps..",
            text: response.message,
            icon: "error",
          });
        }
      },
      (xhr, status, error) => {
        // Error callback
        if (xhr.responseJSON && xhr.responseJSON.message) {
          Swal.fire({
            title: "Opppps..",
            text: xhr.responseJSON.message,
            icon: "error",
          });
        }
      }
    );
  });

  $("#CSQuantity, #IBQuantity, #PCQuantity").on("input", function () {
    autoCalculateTotalPrice();
  });

  $("#rePrintPage").on("click", async function () {
    printPurchaseOrder();
  });

  function printPurchaseOrder(PONumbers) {
    const PONumber = PONumbers ? PONumbers : selectedMain.PONumber;

    // Get screen width and height
    let screenWidth = window.screen.width;
    let screenHeight = window.screen.height;

    // Set popup width and height
    let popupWidth = $(window).width() - $(window).width() * 0.1;
    let popupHeight = $(window).height() - $(window).height() * 0.1;

    // Calculate center position
    let left = (screenWidth - popupWidth) / 2;
    let top = (screenHeight - popupHeight) / 2;

    window.open(
      "api/print/po/" + PONumber, // URL to open
      "popupWindow", // Window name
      `width=${popupWidth},height=${popupHeight}, left = ${left}, top = ${top},toolbar=yes,location=no,status=yes,menubar=no,scrollbars=no,resizable=no`
    );

    // window.open(', "_blank");
  }

  $("#modalFields").on("hidden.bs.modal", function () {
    POModal.enable(false);
  });

  $("#itemTables").on("click", "tbody tr", function (event) {
    // Find the checkbox inside the row
    // var checkbox = $(this).find(".form-check-input");
  });

  $(document).on("click", ".itemDeleteIcon", async function () {
    // console.log($(this).parent());
    const row = $(this).closest("tr");
    const skuCode = row.find("td:first"); // Get the first <td>
    POItemsModal.itemTmpDelete(skuCode);
  });

  $(document).on("click", ".itemUpdateIcon", async function () {
    const row = $(this).closest("tr");
    const itemStockCode = row.find("td:first").text().trim();
    POItemsModal.enable(true);

    $("#CSQuantity").val("");
    $("#IBQuantity").val("");
    $("#PCQuantity").val("");

    POItemsModal.show();

    const select = document.querySelector("#StockCode");

    // Set value programmatically
    select.setValue(itemStockCode);

    // Manually trigger the `afterClose` event
    const event = new CustomEvent("afterClose");
    select.dispatchEvent(event);
  });

  $(document).on("click", "#CustomerNoDataFoundBtn", async function () {
    vendorModal.clear();
    vendorModal.show();
  });

  $(document).on("click", "#addBtn", async function () {
    POModal.enable(true);
    POModal.clear();

    $("#editXmlDataModal").modal("show");

    $("#deleteBtn").hide();
    $("#rePrintPage").hide();
    $("#saveBtn").show();
    $("#editBtn").hide();
    itemTmpSave = [];
    selectedMain = null;

    datatables.initPOItemsDatatable(null);
    $("#confirmPO").hide();
    $("#addItems").show();
    ItemsTH.column(6).visible(true);
  });

  $("#confirmPO").on("click", async function () {
    Swal.fire({
      title: "Confirm Purchase Order",
      text: "Are you sure you want to proceed with this purchase order? This action cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#3085d6",
      cancelButtonColor: "#d33",
      confirmButtonText: "Yes, proceed with the order!",
    }).then(async (result) => {
      if (result.isConfirmed) {
        await ajax(
          "api/orders/po-confirm/" + selectedMain.id,
          "POST",
          null,
          (response) => {
            // Success callback
            if (response.success) {
              datatables.loadPO();
              POModal.hide();

              Swal.fire({
                title: "Success!",
                text: response.message,
                timer: 2000, // Auto close after 2 seconds
                showConfirmButton: true, // Show OK button
                icon: "success",
              }).then(() => {
                printPurchaseOrder();
              });
            }
          },
          (xhr, status, error) => {
            // Error callback

            if (xhr.responseJSON && xhr.responseJSON.message) {
              Swal.fire({
                title: "Opppps..",
                text: xhr.responseJSON.message,
                icon: "error",
              });
            }
          }
        );
      }
    });
  });

  $("#deleteBtn").on("click", async function () {
    if ($(this).text().toLowerCase() == "cancel") {
      $(this).text("Delete");

      $("#editBtn").removeClass("btn-primary").addClass("btn-info");
      $("#editBtn").text("Edit details");

      POModal.fill(selectedMain);
      datatables.initPOItemsDatatable(selectedMain.p_o_items);
      itemTmpSave = selectedMain.p_o_items;
      POModal.enable(false);
      $("#confirmPO").show();
      ItemsTH.column(6).visible(false);
    } else {
      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
      }).then(async (result) => {
        if (result.isConfirmed) {
          ajax(
            "api/orders/po/" + selectedMain.id,
            "POST",
            JSON.stringify({ _method: "DELETE" }),
            (response) => {
              // Success callback
              if (response.success) {
                Swal.fire({
                  title: "Success!",
                  text: response.message,
                  icon: "success",
                });

                datatables.loadPO();
                POModal.hide();
              } else {
                Swal.fire({
                  title: "Opppps..",
                  text: response.message,
                  icon: "error",
                });
              }
            },
            (xhr, status, error) => {
              // Error callback
              if (xhr.responseJSON && xhr.responseJSON.message) {
                Swal.fire({
                  title: "Opppps..",
                  text: xhr.responseJSON.message,
                  icon: "error",
                });
              }
            }
          );
        }
      });
    }
  });

  // Set up a MutationObserver to watch for changes in the container's visibility
  const observer = new MutationObserver(function (mutations) {
    mutations.forEach(function (mutation) {
      if (mutation.attributeName === "style") {
        const isVisible = $("#editXmlDataModal").is(":visible");
        if (isVisible && ItemsTH) {
          ItemsTH.columns.adjust();
          ItemsTH.draw();
        }
      }
    });
  });

  // Start observing the container for attribute changes
  observer.observe(document.getElementById("editXmlDataModal"), {
    attributes: true, // Configure it to listen to attribute changes
  });

  $("#addItems").on("click", function () {
    POItemsModal.clear();
    POItemsModal.enable(true);

    $(".UOMField").addClass("d-none");
    $("#itemSave").text("Save Item");
    POItemsModal.show();

    $("#itemEdit").hide();
    $("#itemSave").show();
  });

  $("#editBtn").on("click", async function () {
    if ($(this).text().toLocaleLowerCase() == "edit details") {
      POModal.enable(true);
      $(this)
        .text("Save changes")
        .removeClass("btn-info")
        .addClass("btn-primary");
      $("#deleteBtn").text("Cancel");
      $("#confirmPO").hide();

      //set the selected vendor to the to be edit vendor
      const modalCurrentVendor = $("#vendorName").val().trim();
      selectedVendor = vendordata.find(
        (item) => (item.id = modalCurrentVendor)
      );
      ItemsTH.column(6).visible(true);
      $("#addItems").show();
    } else {
      //save update
      if (POModal.isValid()) {
        Swal.fire({
          title: "Are you sure?",
          text: "You won't be able to revert this!",
          showDenyButton: true,
          confirmButtonText: "Yes, Update",
          denyButtonText: `Cancel`,
        }).then(async (result) => {
          if (result.isConfirmed) {
            let updateBody = POModal.getData();
            updateBody.Items = itemTmpSave;

            await ajax(
              "api/orders/po/" + selectedMain.id,
              "POST",
              JSON.stringify({
                data: updateBody,
                _method: "PUT",
              }),
              (response) => {
                // Success callback

                if (response.success) {
                  // datatables.loadItems(selectedMain.PONumber);
                  $(this)
                    .text("Edit details")
                    .removeClass("btn-primary")
                    .addClass("btn-info");
                  $("#deleteBtn").text("Delete");

                  Swal.fire({
                    title: "Success!",
                    text: response.message,
                    icon: "success",
                  });

                  $("#confirmPO").show();
                  POModal.hide();
                  datatables.loadPO();

                  ItemsTH.column(6).visible(false);
                } else {
                  Swal.fire({
                    title: "Opppps..",
                    text: response.message,
                    icon: "error",
                  });
                }
              },
              (xhr, status, error) => {
                // Error callback
                if (xhr.responseJSON && xhr.responseJSON.message) {
                  Swal.fire({
                    title: "Opppps..",
                    text: xhr.responseJSON.message,
                    icon: "error",
                  });
                }
              }
            );
          }
        });
      } else {
        Swal.fire({
          title: "Missing Required Fields!",
          text: "Please fill in all fields. Some required fields are empty.",
          icon: "warning",
        });
      }
    }
  });

  $("#Quantity").on("input", function () {
    autoCalculateTotalPrice();
  });

  $("#itemSave").on("click", function () {
    console.log($(this).text().toLowerCase());
    if (
      POItemsModal.isValid() &&
      $("#TotalPrice").val() &&
      parseInt(parseMoney($("#TotalPrice").val())) > 0
    ) {
      const getItem = POItemsModal.getData();
      console.log(getItem);
      if ($(this).text().toLowerCase() == "update item") {
        POItemsModal.itemTmpUpdate(getItem);
      } else {
        getItem.PRD_INDEX = itemTmpSave ? itemTmpSave.length + 1 : 1;
        POItemsModal.itemTmpSave(getItem);
      }
    } else {
      Swal.fire({
        title: "Missing Required Fields!",
        text: "Please fill in all fields. Some required fields are empty.",
        icon: "warning",
      });
    }
  });

  $("#saveBtn").on("click", function () {
    if (POModal.isValid()) {
      if (itemTmpSave.length < 1) {
        Swal.fire({
          title: "No items",
          text: "Please review your order. No items have been added for purchase.",
          icon: "error",
        });
      } else {
        Swal.fire({
          title: "Are you sure?",
          text: "Do you want to add this data?",
          icon: "question",
          showDenyButton: true,
          confirmButtonText: "Yes, Add",
          denyButtonText: `Cancel`,
        }).then(async (result) => {
          if (result.isConfirmed) {
            POModal.POSave();
          }
        });
      }
    } else {
      Swal.fire({
        title: "Missing Required Fields!",
        text: "Please fill in all fields. Some required fields are empty.",
        icon: "warning",
      });
    }
  });

  $("#newVendorSaveBtn").on("click", function () {
    if (vendorModal.isValid()) {
      Swal.fire({
        title: "Are you sure?",
        text: "Do you want to add this data?",
        icon: "question",
        showDenyButton: true,
        confirmButtonText: "Yes, Add",
        denyButtonText: `Cancel`,
      }).then(async (result) => {
        if (result.isConfirmed) {
          vendorModal.newVendorSave();
        }
      });
    } else {
      Swal.fire({
        title: "Missing Required Fields!",
        text: "Please fill in all fields. Some required fields are empty.",
        icon: "warning",
      });
    }
  });

  $(".fa-minus").on("click", function () {
    const quantityElement = $(this).closest(".input-group").find("input");
    let quantity = quantityElement.val();

    if (quantity && parseInt(quantity) > 0) {
      quantityElement.val(parseInt(quantity) - 1);
      autoCalculateTotalPrice();
    }
  });

  $(".fa-plus").on("click", function () {
    const quantity = $(this).closest(".input-group").find("input");
    quantity.val(quantity.val() ? parseInt(quantity.val()) + 1 : 1);
    autoCalculateTotalPrice();
  });

  // Prevent modal from closing when clicking the secondary button
  $("#poCloseBtn").on("click", function () {
    if (
      (!$("#deleteBtn").is(":visible") && !$("#rePrintPage").is(":visible")) ||
      $("#deleteBtn").text().toLowerCase() == "cancel"
    ) {
      let valid = false;
      if (selectedVendor) {
        const data = POModal.getData();

        // Check for empty values (excluding totalDiscount since it's always 0)
        for (const key in data) {
          if (
            data[key] === "" ||
            data[key] === null ||
            data[key] === undefined
          ) {
            valid = true; // Stop execution if a field is missing
          }
        }
      }

      if (valid) {
        Swal.fire({
          title: "Are you sure?",
          text: "You want to close? Unsaved data will be erased.",
          icon: "question",
          showDenyButton: true,
          confirmButtonText: "Yes, Close",
          denyButtonText: `Cancel`,
        }).then(async (result) => {
          if (result.isConfirmed) {
            POModal.hide();
          }
        });
      } else {
        POModal.hide();
      }
    } else {
      POModal.hide();
    }
  });

  $("#itemCloseBtn").on("click", function () {
    let valid = false;
    const data = POItemsModal.getData();

    // Check for empty values (excluding totalDiscount since it's always 0)
    for (const key in data) {
      if (data[key] === "" || data[key] === null || data[key] === undefined) {
        valid = true; // Stop execution if a field is missing
      }
    }

    if (valid) {
      Swal.fire({
        title: "Are you sure?",
        text: "You want to close? Unsaved data will be erased.",
        icon: "question",
        showDenyButton: true,
        confirmButtonText: "Yes, Close",
        denyButtonText: `Cancel`,
      }).then(async (result) => {
        if (result.isConfirmed) {
          POItemsModal.hide();
        }
      });
    }
  });
});

function autoCalculateTotalPrice() {
  const uoms = POItemsModal.getUOM();
  const totalInPieces = POItemsModal.getTotalQuantity(uoms);
  $("#TotalPrice").val(
    formatMoney(($("#PricePerUnit").val() || 0) * totalInPieces)
  );
}

async function getProductPriceCodes() {
  await ajax(
    "api/getProductPriceCodes",
    "GET",
    null,
    (response) => {
      priceCodes = response.success && response.data;
    },
    (xhr, status, error) => {
      // Error callback
      console.error("Error:", error);
    }
  );
}

function calculateCost() {
  const taxCost = 0;
  const shippingCost = 0;
  const othersCost = 0;
  const grandTotal = ItemsTH.rows()
    .data()
    .toArray()
    .reduce((sum, item) => sum + parseFloat(item.TotalPrice), 0);

  $("#taxCost").text(formatMoney(taxCost));
  $("#shippingCost").text(formatMoney(shippingCost));
  $("#othersCost").text(formatMoney(othersCost));
  $("#grandTotal").text(formatMoney(grandTotal));
  $("#subTotal").text(formatMoney(grandTotal));
}

const initVS = {
  liteDataVS: async () => {
    // Initialize VirtualSelect for ship via
    VirtualSelect.init({
      ele: "#shipVia", // Attach to the element
      options: [
        { label: "Road Delivery", value: "road_delivery" },
        { label: "Air Freight", value: "air_freight" },
      ], // Provide options
      maxWidth: "100%", // Set maxWidth
      multiple: false, // Enable multiselect
      hideClearButton: true, // Hide clear button
      disabledOptions: ["air_freight"],
      selectedValue: "road_delivery", // Preselect (must match `value`)
    });

    // Initialize VirtualSelect for filter po
    VirtualSelect.init({
      ele: "#filterPOVS", // Attach to the element
      options: [
        { label: "Pending PO", value: null },
        { label: "Confirmed PO", value: 1 },
        { label: "other status PO", value: 2 },
      ], // Provide options
      multiple: true, // Enable multiselect
      hideClearButton: true, // Hide clear button
      search: false,
      maxWidth: "100%", // Set maxWidth
      additionalClasses: "rounded",
      additionalDropboxClasses: "rounded",
      additionalDropboxContainerClasses: "rounded",
      additionalToggleButtonClasses: "rounded",
      // selectedValue: 'road_delivery',    // Preselect (must match `value`)
    });

    vendorModal.loadVendorVS();

    $("#vendorName").on("afterClose", function () {
      if (this.value) {
        var findVendor = vendordata.find((item) => item.cID == this.value);
        const validPriceCode = priceCodes.some(
          (item) => item.PRICECODE == findVendor.PriceCode.trim()
        );
        if (validPriceCode) {
          $("#VendorContactName").val(findVendor.ContactPerson);
          $("#vendorAddress").val(findVendor.CompleteAddress);

          var mobileContact = (findVendor.ContactNo = /^9\d{9}$/.test(
            findVendor.ContactNo
          )
            ? findVendor.ContactNo.replace(/^9/, "09")
            : findVendor.ContactNo);

          selectedVendor = findVendor;

          $("#vendorPhone").val(mobileContact);

          if (this.value && $("#shippedToName").value) {
            $("#addItems").prop("disabled", false);
          }

          $("#shippingTerms").val(findVendor.TermsCode);
        } else {
          selectedVendor = null;
          Swal.fire({
            title: "Opppps..",
            text: "The selected vendor has an invalid price code.",
            icon: "warning",
          });
          $("#vendorName").trigger("reset");
        }
      } 
    });

    $("#vendorName").on("reset", function () {
      $("#VendorContactName").val("");
      $("#vendorAddress").val("");
      $("#vendorPhone").val("");
      selectedVendor = null;
    });

    $("#filterPOVS").on("change", async function () {
      if (this.value) {
        let filterValue = this.value;

        filterValue = filterValue.map((item) => (item === "" ? null : item));

        await ajax(
          "api/orders/po-filter",
          "POST",
          JSON.stringify(filterValue),
          (response) => {
            // Success callback
            ajaxMainData = response.data ?? null;
            datatables.initPODatatable(ajaxMainData);
            console.log(filterValue);
          },
          (xhr, status, error) => {
            // Error callback
            console.error("Error:", error);
          }
        );
      }
    });

    //shippedToData
    await ajax(
      "api/supplier-shipped-to",
      "GET",
      null,
      (response) => {
        // Success callback
        shippedToData = response.data;

        const newData = response.data.map((item) => {
          // Create a new object with the existing properties and the new column
          return {
            value: item.cID, // Spread the existing properties
            label: item.CompleteAddress, // Copy the value from sourceKey to targetKey
          };
        });

        // Check if the VirtualSelect instance exists before destroying
        if (document.querySelector("#shippedToName")?.virtualSelect) {
          document.querySelector("#shippedToName").destroy();
        }

        VirtualSelect.init({
          ele: "#shippedToName",
          options: newData,
          markSearchResults: true,
          maxWidth: "100%",
          search: true,
          autofocus: true,
          hasOptionDescription: true,
          noSearchResultsText: `<div class="w-100 d-flex justify-content-around align-items-center mt-2">
                                    <div class="w-auto text-center">
                                         No result found. Add new?
                                    </div>
                                    <div class="w-auto">
                                        <button id="ShipperNoDataFoundBtn" type="button" class="btn btn-primary btn-sm">Add new</button>
                                    </div>
                                </div>`,
        });

        $("#shippedToName").on("afterClose", function () {
          if (this.value) {
            var findSupplier = response.data.find(
              (item) => item.cID == this.value
            );

            $("#shippedToContactName").val(findSupplier.ContactPerson.trim());
            $("#shippedToAddress").val(findSupplier.CompleteAddress.trim());

            var mobileContact = (findSupplier.ContactNo = /^9\d{9}$/.test(
              findSupplier.ContactNo
            )
              ? findSupplier.ContactNo.replace(/^9/, "09")
              : findSupplier.ContactNo);

            $("#shippedToPhone").val(mobileContact);

            if (this.value && document.querySelector("#vendorName").value) {
              $("#addItems").prop("disabled", false);
            }

            console.log(this.value);
          }
        });

        $("#shippedToName").on("reset", function () {
          $("#shippedToContactName").val("");
          $("#shippedToAddress").val("");
          $("#shippedToPhone").val("");
        });
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },

  bigDataVS: async () => {
    await ajax(
      "api/product",
      "GET",
      null,
      (response) => {
        // Success callback
        const products = response.data;

        const newData = products.map((item) => {
          // Create a new object with the existing properties and the new column
          return {
            description: item.Description,
            value: item.StockCode, // Spread the existing properties
            label: item.StockCode, // Copy the value from sourceKey to targetKey
          };
        });

        // Check if the VirtualSelect instance exists before destroying
        if (document.querySelector("#StockCode")?.virtualSelect) {
          document.querySelector("#StockCode").destroy();
        }

        // Initialize VirtualSelect
        VirtualSelect.init({
          ele: "#StockCode", // Attach to the element
          options: newData, // Provide options
          maxWidth: "100%", // Set maxWidth
          autofocus: true,
          search: true,
          hasOptionDescription: true,
        });

        $("#StockCode").on("afterClose", async function () {
          if (this.value) {
            const stockCode = this.value;
            var findProduct = products.find(
              (item) => item.StockCode == stockCode
            );
            $("#Decription").val(findProduct.Description);

            let priceCode = selectedVendor.PriceCode.trim();

            const getPriceBody = {
              stockCode: stockCode,
              priceCode: priceCode,
            };

            await ajax(
              "api/getProductPrice",
              "GET",
              getPriceBody,
              (response) => {
                // Success callback

                if (response.status_response == 1) {
                  //UOM Dorpdown
                  var uomColumn = ["StockUom", "AlternateUom", "OtherUom"];

                  // Create a new object with the existing properties and the new column
                  var uoms = uomColumn.map((item) => {
                    return {
                      value: findProduct[item], // Spread the existing properties
                      label: findProduct[item], // Copy the value from sourceKey to targetKey
                    };
                  });

                  // Remove duplicates from `newData` based on the `value` property
                  uoms = uoms.filter(
                    (item, index, self) =>
                      index ===
                      self.findIndex((other) => other.value === item.value)
                  );

                  if (!$(".UOMField").hasClass("d-none")) {
                    $(".UOMField").addClass("d-none");
                    $("#itemModalFields").validate().resetForm();
                  }

                  uoms.forEach((item) => {
                    $(`#${item.value}Div`).removeClass("d-none");
                  });

                  productConFact = response.convertionFactor;
                  console.log(productConFact);
                  console.log(response.convertionFactor);

                  $("#PricePerUnit").val(response.response.UNITPRICE);
                  $("#itemSave").prop("disabled", false);

                  const isAlreadyExist = itemTmpSave.find(
                    (item) => item.StockCode == stockCode
                  );

                  if (isAlreadyExist) {
                    selectedItem = isAlreadyExist;
                    POItemsModal.itemEditMode(uoms, isAlreadyExist);
                  } else {
                    if ($("#itemSave").text().toLowerCase() == "update item") {
                      $("#itemSave").text("Save Item");
                    }
                  }

                  // autoCalculateTotalPrice();
                } else {
                  $("#PricePerUnit").val("");
                  $("#itemSave").prop("disabled", true);

                  Swal.fire({
                    title: "Opppps..",
                    text: "No price maintained for this product",
                    icon: "warning",
                  });
                }
              },
              (xhr, status, error) => {
                $("#PricePerUnit").val("");
                $("#itemSave").prop("disabled", true);

                Swal.fire({
                  title: "Opppps..",
                  text: "No price maintained for this product",
                  icon: "warning",
                });
              }
            );

            autoCalculateTotalPrice();
          }
        });

        $("#StockCode").on("reset", function () {
          $("#Decription").val("");
          $("#PricePerUnit").val("");
        });
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },
};

const POModal = {
  isValid: () => {
    return $("#modalFields").valid();
  },
  hide: () => {
    $("#editXmlDataModal").modal("hide");
  },
  show: () => {
    $("#editXmlDataModal").modal("show");
  },
  fill: async (POData) => {
    const findVendor = vendordata.find(
      (item) => item.SupplierCode.trim() == POData.SupplierCode.trim()
    );
    selectedVendor = findVendor;
    document.querySelector("#vendorName").setValue(findVendor.cID);
    $("#VendorContactName").val(findVendor.ContactPerson);
    $("#vendorAddress").val(findVendor.CompleteAddress);
    $("#vendorPhone").val(findVendor.ContactNo);

    $("#shippedToContactName").val(POData.contactPerson);
    $("#shippedToAddress").val(POData.deliveryAddress);
    $("#shippedToName").val(POData.deliveryAddress);
    document.querySelector("#shippedToName").setValue(POData.deliveryAddress);

    $("#shippedToPhone").val(POData.contactNumber);
    $("#deliveryMethod").val(POData.deliveryMethod);

    $("#requisitioner").val(POData.orderPlacer);
    $("#fob").val(POData.FOB);
    $("#subTotal").text(formatMoney(POData.subTotal));
    $("#totalDiscount").val(formatMoney(POData.totalDiscount));
    $("#others").val(formatMoney(POData.othersCost));
    $("#grandTotal").text(formatMoney(POData.totalCost));
    $("#poComment").val(POData.SpecialInstruction);
    $("#taxCost").text(formatMoney(POData.totalTax));
    $("#shippingTerms").val(POData.TermsCode);
  },
  clear: () => {
    $('#modalFields input[type="text"]').val("");
    $('#modalFields input[type="number"]').val("");
    $("#modalFields textarea").val("");

    if (document.querySelector("#vendorName")?.virtualSelect) {
      document.querySelector("#vendorName").reset();
    }

    if (document.querySelector("#shippedToName")?.virtualSelect) {
      document.querySelector("#shippedToName").reset();
    }

    $("#subTotal").text(formatMoney(0));
    $("#taxCost").text(formatMoney(0));
    $("#totalItemsLabel").text("0");
    $("#grandTotal").text(formatMoney(0));
  },
  enable: (enable) => {
    $('#modalFields input[type="text"]').prop("disabled", !enable);
    $('#modalFields input[type="number"]').prop("disabled", !enable);
    $("#modalFields textarea").prop("disabled", !enable);
    $("#itemTables").find('input[type="checkbox"]').prop("disabled", !enable);

    $("#addItems").prop("disabled", !enable);

    if (enable) {
      document.querySelector("#vendorName").enable();
      document.querySelector("#shippedToName").enable();
      document.querySelector("#shipVia").enable();
    } else {
      document.querySelector("#vendorName").disable();
      document.querySelector("#shippedToName").disable();
      document.querySelector("#shipVia").disable();
    }
  },
  getData: () => {
    var user = JSON.parse(localStorage.getItem("user"));
    var data = {
      // PODate: moment().format("YYYY-MM-DD"),
      SupplierCode: selectedVendor.SupplierCode.trim(),
      SupplierName: selectedVendor.SupplierName.trim(),
      productType: selectedVendor.SupplierType.trim(),
      FOB: $("#fob").val(),
      deliveryAddress: $("#shippedToAddress").val(),
      contactPerson: $("#shippedToContactName").val(),
      contactNumber: $("#shippedToPhone").val(),
      deliveryMethod: $("#shipVia").val(),
      totalDiscount: 0,
      totalTax: parseMoney($("#taxCost").text()),
      SpecialInstruction: $("#poComment").val(),
      EncoderID: user.user_id,
      orderPlacer: $("#requisitioner").val(),
      // orderPlacerEmail: user.email,
      subTotal: parseMoney($("#subTotal").text()),
      TermsCode: $("#shippingTerms").val(),
      totalCost: parseMoney($("#grandTotal").text()),
    };

    return data;
  },
  viewMode: async (POData) => {
    POModal.fill(POData);
    datatables.initPOItemsDatatable(POData.p_o_items);
    itemTmpSave = POData.p_o_items;
    $("#deleteBtn").show();
    $("#saveBtn").hide();
    $("#editBtn").show();
    $("#editBtn")
      .text("Edit details")
      .removeClass("btn-primary")
      .addClass("btn-info");
    ItemsTH.column(6).visible(false);
    $("#addItems").hide();

    if (POData.POStatus == null) {
      $("#confirmPO").show();
      $("#itemBtns").show();
      // ItemsTH.column(6).visible(true);
      $("#rePrintPage").hide();
    } else {
      $("#itemBtns").hide();
      // ItemsTH.column(6).visible(false);
      $("#confirmPO").hide();
      $("#editBtn").hide();
      $("#deleteBtn").hide();
      $("#rePrintPage").show();
    }

    itemTmpSave = ItemsTH.rows().data().toArray();

    POModal.enable(false);
    POModal.show();
  },
  POSave: async () => {
    let POData = POModal.getData();
    POData.Items = itemTmpSave.map((item, index) => ({
      ...item, // ✅ Spread all properties from item
      PRD_INDEX: index + 1, // ✅ Add the new index property
    }));

    POData.orderPlacerEmail = "isItUserEmail@email.com";

    await ajax(
      "api/orders/po",
      "POST",
      JSON.stringify({ data: POData }),
      (response) => {
        // Success callback
        if (response.success) {
          datatables.loadPO();
          POModal.hide();

          Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
          });
        }
      },
      (xhr, status, error) => {
        // Error callback

        if (xhr.responseJSON && xhr.responseJSON.message) {
          Swal.fire({
            title: "Opppps..",
            text: xhr.responseJSON.message,
            icon: "error",
          });
        }
      }
    );
  },
};

const POItemsModal = {
  setValidator: () => {
    // Step 1: Create a custom validation method
    $.validator.addMethod(
      "atLeastOneFilled",
      function (value, element) {
        // Get values of the three elements
        var csQuantity = $("#CSQuantity").val();
        var ibQuantity = $("#IBQuantity").val();
        var pcQuantity = $("#PCQuantity").val();

        // Check if at least one has a value
        return csQuantity !== "" || ibQuantity !== "" || pcQuantity !== "";
      },
      "At least one quantity field is required."
    ); // Custom error message

    // Step 2: Initialize jQuery Validate on the form
    $("#itemModalFields").validate({
      rules: {
        CSQuantity: {
          atLeastOneFilled: true, // Apply the custom validation to the first field
        },
        IBQuantity: {
          atLeastOneFilled: true, // Apply the custom validation to the second field
        },
        PCQuantity: {
          atLeastOneFilled: true, // Apply the custom validation to the third field
        },
      },
      messages: {
        CSQuantity: {
          atLeastOneFilled: "At least one quantity field is required.", // Error message for CSQuantity
        },
        IBQuantity: {
          atLeastOneFilled: "At least one quantity field is required.", // Error message for IBQuantity
        },
        PCQuantity: {
          atLeastOneFilled: "At least one quantity field is required.", // Error message for PCQuantity
        },
      },
      submitHandler: function (form) {
        alert("Form is valid!");
        form.submit(); // Proceed with form submission if validation passes
      },
    });
  },
  isValid: () => {
    return $("#itemModalFields").valid();
  },
  hide: () => {
    $("#itemModal").modal("hide");
  },
  show: () => {
    $("#itemModal").modal("show");
  },
  fill: (itemData) => {
    $("#Decription").val(itemData.Decription);
    $("#Quantity").val(itemData.Quantity);
    $("#UOM").val(itemData.UOM);
    $("#ItemVolume").val(itemData.ItemVolume);
    $("#ItemWeight").val(itemData.ItemWeight);
    $("#TotalPrice").val(itemData.TotalPrice);
    $("#PricePerUnit").val(itemData.PricePerUnit);
    document.querySelector("#StockCode").setValue(itemData.StockCode);
  },
  clear: () => {
    $('#itemModalFields input[type="text"]').val("");
    $('#itemModalFields input[type="number"]').val("");
    $("#itemModalFields textarea").val("");

    if (document.querySelector("#UOMDropDown")?.virtualSelect) {
      document.querySelector("#UOMDropDown").reset();
    }

    if (document.querySelector("#StockCode")?.virtualSelect) {
      document.querySelector("#StockCode").reset();
    }
  },
  enable: (enable) => {
    $('#itemModalFields input[type="text"]').prop("disabled", !enable);
    $('#itemModalFields input[type="number"]').prop("disabled", !enable);
    $("#itemModalFields textarea").prop("disabled", !enable);
  },
  fill: async (POItemData) => {
    $("#CSQuantity").val(POItemData.CS);
    $("#IBQuantity").val(POItemData.IB);
    $("#PCQuantity").val(POItemData.PC);
    $("#StockCode").val(POItemData.StockCode);
    $("#Decription").val(POItemData.Decription);
    $("#Quantity").val(POItemData.Quantity);
    $("#PricePerUnit").val(POItemData.PricePerUnit);
    $("#TotalPrice").val(POItemData.TotalPrice);
  },
  getUOM: () => {
    let UomAndQuantity = {
      CS: $("#CSQuantity").val(),
      IB: $("#IBQuantity").val(),
      PC: $("#PCQuantity").val(),
    };

    UomAndQuantity = Object.fromEntries(
      Object.entries(UomAndQuantity).filter(([_, value]) => value)
    );

    return UomAndQuantity;
  },
  getData: () => {
    const getUOM = POItemsModal.getUOM();

    return {
      StockCode: $("#StockCode").val().trim(),
      Decription: $("#Decription").val(),
      UomAndQuantity: getUOM,
      Quantity: $("#Quantity").val(),
      PricePerUnit: $("#PricePerUnit").val(),
      TotalPrice: parseMoney($("#TotalPrice").val()),
    };
  },
  itemCalculateUOM: (getItem) => {
    const uomsAndQty = getItem.UomAndQuantity;
    const ConvFactAltUom = productConFact.ConvFactAltUom;
    const ConvFactOthUom = productConFact.ConvFactOthUom;

    const totalInPieces = POItemsModal.getTotalQuantity(uomsAndQty);

    if (uomsAndQty.CS) {
      getItem.UOM = "CS";
      getItem.Quantity = (totalInPieces / ConvFactAltUom).toFixed(2);
    } else if (uomsAndQty.IB) {
      getItem.UOM = "IB";
      getItem.Quantity = (
        totalInPieces /
        (ConvFactAltUom / ConvFactOthUom)
      ).toFixed(2);
    } else if (uomsAndQty.PC) {
      getItem.UOM = "PC";
      getItem.Quantity = totalInPieces;
    }

    return getItem;
  },

  reverseItemCalculateUOM: (uoms, totalInPieces) => {
    console.log("totalINPIECS: "+ totalInPieces);
    var moduloResult = 0;
    totalInPieces = selectedItem.TotalQtyInPCS;
    let UomAndQuantity = {};

    const ConvFactAltUom = productConFact.ConvFactAltUom;
    const ConvFactOthUom = productConFact.ConvFactOthUom;

    uoms.forEach((element) => {
      if (element.value === "CS") {
        // Handle Case (CS) - Largest unit
        const getCS = Math.floor(totalInPieces / ConvFactAltUom);
        UomAndQuantity.CS = getCS;
        moduloResult = totalInPieces % ConvFactAltUom;
        // console.log(`CS: ${totalInPieces} / ${ConvFactAltUom} = ${totalInPieces / ConvFactAltUom}`);
        // console.log(`CS: ${totalInPieces} % ${ConvFactAltUom} = ${totalInPieces % ConvFactAltUom}`);
      } else if (element.value === "IB") {
        // Handle Intermediate Unit (IB)
        const conFact = ConvFactAltUom / ConvFactOthUom; // Calculate conversion factor between IB and CS
        moduloResult = moduloResult > 0 ? moduloResult : totalInPieces;

        const getIB = Math.floor(moduloResult / conFact);
        UomAndQuantity.IB = getIB;

        moduloResult = moduloResult % conFact;

        // console.log(`IB: ${moduloResult} / ${ConvFactAltUom} = ${moduloResult / ConvFactAltUom}`);
        // console.log(`IB: ${moduloResult} % ${ConvFactAltUom} = ${moduloResult % ConvFactAltUom}`);
      } else if (element.value === "PC") {
        UomAndQuantity.PC = moduloResult;
        // console.log(`PC: ${moduloResult}`);
      }
    });

    console.log(uoms);

    return UomAndQuantity;
  },
  itemTmpSave: (getItem) => {
    getItem = POItemsModal.itemCalculateUOM(getItem);
    itemTmpSave.unshift(getItem);
    datatables.initPOItemsDatatable(itemTmpSave);
    calculateCost();
    POItemsModal.hide();
  },
  itemTmpUpdate: (editedItem) => {
    // Optionally, if you want to reflect the change in currentItems
    editedItem = POItemsModal.itemCalculateUOM(editedItem);
    itemTmpSave = itemTmpSave.map((item) =>
      item.StockCode === selectedItem.StockCode
        ? { ...item, ...editedItem }
        : item
    );

    datatables.initPOItemsDatatable(itemTmpSave);
    POItemsModal.hide();
  },
  itemEditMode: (uoms, isAlreadyExist) => {
    console.log(isAlreadyExist);

    if (!isAlreadyExist.UomAndQuantity) {
      isAlreadyExist.UomAndQuantity = POItemsModal.reverseItemCalculateUOM(
        uoms,
        isAlreadyExist.TotalQtyInPCS
      );
    }

    Object.entries(isAlreadyExist.UomAndQuantity).forEach(([key, value]) => {
      $(`#${key}Quantity`).val(value);
    });

    $("#itemSave").text("Update Item");
  },

  itemTmpDelete: (skuCode) => {
    itemTmpSave = itemTmpSave.filter(
      (item) => item.StockCode != skuCode.text()
    );

    datatables.initPOItemsDatatable(itemTmpSave);
    calculateCost();
  },

  itemApiUpdate: async (editedItem) => {
    await ajax(
      "api/orders/po-items/" + editedItem.id,
      "POST",
      JSON.stringify({ data: item }),
      (response) => {
        // Success callback
        if (response.success) {
          Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
          });

          POItemsModal.hide();
          datatables.loadItems(editedItem.PONumber);
          calculateCost();
        }
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },

  itemAPISave: async (item) => {
    item.PONumber = selectedMain.PONumber;
    item.PRD_INDEX = ItemsTH.rows().data().toArray().length + 1;

    await ajax(
      "api/orders/po-items",
      "POST",
      JSON.stringify({ data: item }),
      (response) => {
        // Success callback
        if (response.success) {
          Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
          });

          POItemsModal.hide();
          datatables.loadItems(item.PONumber);
          calculateCost();
        }
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },

  itemApiDelete: async (itemId) => {
    await ajax(
      "api/orders/po-items/" + itemId,
      "POST",
      JSON.stringify({ _method: "DELETE" }),
      async (response) => {
        // Success callback
        // Now, show success message after deletion is truly completed
        if (response.success) {
          Swal.fire({
            title: "Deleted!",
            text: response.message,
            icon: "success",
            confirmButtonColor: "#3085d6",
          });

          await datatables.loadItems(selectedMain.PONumber);
          calculateCost();
        } else {
          Swal.fire({
            title: "Opppps..",
            text: response.message,
            icon: "error",
          });
        }
      },
      (xhr, status, error) => {
        // Error callback
        Swal.fire({
          title: "Opppps..",
          text: xhr.responseJSON.message,
          icon: "error",
        });
      }
    );
  },

  getTotalQuantity: (UomAndQuantity) => {
    const ConvFactAltUom = productConFact.ConvFactAltUom;
    const ConvFactOthUom = productConFact.ConvFactOthUom;
    let totalInPieces = 0;

    Object.entries(UomAndQuantity).forEach(([key, uom]) => {
      if (key.toUpperCase() === "PC") {
        totalInPieces += Number(uom);
      } else if (key.toUpperCase() === "IB") {
        totalInPieces += (ConvFactAltUom / ConvFactOthUom) * Number(uom);
      } else if (key.toUpperCase() === "CS") {
        totalInPieces += Number(uom) * ConvFactAltUom;
      }
    });
    return totalInPieces;
  },
};

// function quantityBtn(quantity){
//     const btn = this.closest('input');
//     console.log(btn);
// }

const datatables = {
  loadPO: async () => {
    const poData = await ajax(
      "api/orders/po",
      "GET",
      null,
      (response) => {
        // Success callback
        if (response.success) {
          ajaxMainData = response.data;
          datatables.initPODatatable(ajaxMainData);
        }
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },
  loadItems: async (PONumber) => {
    const poItems = await ajax(
      "api/orders/po-items/search-items/" + PONumber,
      "GET",
      null,
      (response) => {
        // Success callback
        ajaxItemsData = response.data;
        datatables.initPOItemsDatatable(ajaxItemsData);
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },
  initPODatatable: (response) => {
    if (MainTH) {
      MainTH.clear().draw();
      MainTH.rows.add(response).draw();
    } else {
      MainTH = $("#POHeaderTable").DataTable({
        data: response,
        layout: {
          topStart: function () {
            return $(dataTableCustomBtn);
          },
        },
        columns: [
          { data: "OrderNumber" },
          { data: "PONumber" },
          {
            data: "POStatus",
            render: function (data, type, row) {
              return data != null ? "<span class='statusBadge1'>Confirmed</span>" : "<span class='statusBadge3'>Pending</span>";
            },
          },
          { data: "SupplierName" },
          { data: "PODate" },
          { data: "orderPlacer" },
          {
            data: "totalDiscount",
            render: function (data, type, row) {
              return formatMoney(data);
            },
          },
          {
            data: "totalCost",
            render: function (data, type, row) {
              return formatMoney(data);
            },
          },
        ],
        columnDefs: [
          { className: "text-center", targets: [2] },
          { className: "text-end", targets: [5, 6] }
        ],
        scrollCollapse: true,
        scrollY: "100%",
        scrollX: "100%",
        createdRow: function (row, data) {
          $(row).attr("id", data.id);
        },

        pageLength: 15,
        lengthChange: false,

        initComplete: function () {
          $(this.api().table().container())
            .find("#dt-search-0")
            .addClass("p-1 mx-0 dtsearchInput nofocus");
          $(this.api().table().container())
            .find(".dt-search label")
            .addClass("py-1 px-3 mx-0 dtsearchLabel");
          $(this.api().table().container())
            .find(".dt-layout-row")
            .addClass("px-4");
          $(this.api().table().container())
            .find(".dt-layout-table")
            .removeClass("px-4");
          $(this.api().table().container())
            .find(".dt-scroll-body")
            .addClass("rmvBorder");
          $(this.api().table().container())
            .find(".dt-layout-table")
            .addClass("btmdtborder");

          // Select the label element and replace it with a div
          $(".dt-search label").replaceWith(function () {
            return $("<div>", {
              html: $(this).html(),
              id: $(this).attr("id"),
              class: $(this).attr("class"),
            });
          });
          const dtlayoutTE = $(".dt-layout-cell.dt-end").first();
          dtlayoutTE.addClass("d-flex justify-content-end");
          dtlayoutTE.prepend(
            '<div id="filterPOVS" name="filter" style="width: 200px" class="form-control bg-white p-0 mx-1">Filter</div>'
          );
          $(this.api().table().container())
            .find(".dt-search")
            .addClass("d-flex justify-content-end");
          $(".loadingScreen").remove();
          $("#dattableDiv").removeClass("opacity-0");
        },
      });
    }
  },
  initPOItemsDatatable: (datas) => {
    if (ItemsTH) {
      ItemsTH.clear().draw();
      datas && ItemsTH.rows.add(datas).draw();
    } else {
      ItemsTH = $("#itemTables").DataTable({
        data: datas,
        columns: [
          { data: "StockCode" },
          { data: "Decription" },
          { data: "Quantity" },
          { data: "UOM" },
          {
            data: "PricePerUnit",
            render: function (data, type, row) {
              return formatMoney(data);
            },
          },
          {
            data: "TotalPrice",
            render: function (data, type, row) {
              return formatMoney(data);
            },
          },
          {
            data: null, // Placeholder for checkbox
            render: function (data, type, row) {
              // return '<div class="form-check d-flex justify-content-center align-items-center"><input type="checkbox" class="form-check-input row-checkbox cursor-pointer hover:bg-light" data-id="' + row.id + '"></div>';

              return ` <div class="d-flex actIcon">
                                        <div class="w-50 d-flex justify-content-center itemUpdateIcon">
                                            <i class="fa-regular fa-pen-to-square fa-lg text-primary m-auto "></i>
                                        </div>
                                        <div class="w-50 d-flex justify-content-center itemDeleteIcon">
                                            <i class="fa-solid fa-trash fa-lg text-danger m-auto"></i>
                                        </div>
                                    </div>`;
            },
            orderable: false, // Prevent sorting on the checkbox column
            searchable: false, // Disable search on the checkbox column
            createdCell: function (
              cell,
              cellData,
              rowData,
              rowIndex,
              colIndex
            ) {
              // Add class to the parent <td> element dynamically
              $(cell).addClass("nhover");
            },
          },
        ],
        columnDefs: [
          { className: "text-start", targets: [0, 1] },
          { className: "text-center", targets: [2, 3] },
          { className: "text-end", targets: [ 4, 5] },
        ],
        searching: false,
        scrollCollapse: true,
        responsive: true, // Enable responsive modeoWidth: true, // Enable auto-width calculation
        scrollY: "40vh",
        scrollX: "100%",
        createdRow: function (row, data) {
          $(row).attr("id", data.id);
        },
        lengthChange: false, // Hides the per page dropdown
        info: false, // Hides the bottom text (like "Showing x to y of z entries")
        paging: false, // Hides the pagination controls (Next, Previous, etc.)
      });
    }

    $("#totalItemsLabel").text(datas ? datas.length : 0);
  },
};

const vendorModal = {
  loadVendorVS: async () => {
    await ajax(
      "api/vendors",
      "GET",
      null,
      (response) => {
        // Success callback
        // shippedToData = response.data;
        vendordata = response.data;
        const newData = vendordata.map((item) => {
          // Create a new object with the existing properties and the new column
          return {
            description: item.CompleteAddress,
            value: item.cID, // Spread the existing properties
            label: item.SupplierName, // Copy the value from sourceKey to targetKey
          };
        });

        // Check if the VirtualSelect instance exists before destroying
        if (document.querySelector("#vendorName")?.virtualSelect) {
          document.querySelector("#vendorName").destroy();
        }

        VirtualSelect.init({
          ele: "#vendorName",
          options: newData,
          markSearchResults: true,
          maxWidth: "100%",
          search: true,
          autofocus: true,
          hasOptionDescription: true,
          noSearchResultsText: `<div class="w-100 d-flex justify-content-around align-items-center mt-2">
                                                <div class="w-auto text-center">
                                                     No result found. Add new?
                                                </div>
                                                <div class="w-auto">
                                                    <button id="CustomerNoDataFoundBtn" type="button" class="btn btn-primary btn-sm">Add new</button>
                                                </div>
                                            </div>`,
        });
      },
      (xhr, status, error) => {
        // Error callback
        console.error("Error:", error);
      }
    );
  },
  isValid: () => {
    return $("#newVendorForm").valid();
  },
  show: () => {
    $("#newVendorModal").modal("show");
  },
  hide: () => {
    $("#newVendorModal").modal("hide");
  },
  clear: () => {
    document.querySelector("#Region").reset();
    document.querySelector("#Province").reset();
    document.querySelector("#CityMunicipality").reset();
    document.querySelector("#Barangay").reset();

    $('#newVendorForm input[type="text"]').val("");
    $('#newVendorForm input[type="number"]').val("");
    $("#newVendorForm textarea").val("");
  },
  getData: () => {
    return {
      SupplierName: $("#SupplierName").val(),
      SupplierType: $("#SupplierType").val(),
      TermsCode: $("#TermsCode").val(),
      ContactPerson: $("#ContactPerson").val(),
      ContactNo: $("#ContactNo").val(),
      CompleteAddress: $("#NVCompleteAddress").val(),
      Region: $("#Region").val(),
      Province: $("#Province").val(),
      City: $("#CityMunicipality").val(),
      Municipality: $("#CityMunicipality").val(),
      Barangay: $("#Barangay").val(),
      PriceCode: $("#newVendorPriceCode").val(),
    };
  },
  newVendorSave: async () => {
    const newVendor = vendorModal.getData();

    await ajax(
      "api/vendors",
      "POST",
      JSON.stringify({ newVendor }),
      (response) => {
        // Success callback
        if (response.success) {
          vendorModal.loadVendorVS();
          vendorModal.hide();

          Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
          });
        }
      },
      (xhr, status, error) => {
        // Error callback

        if (xhr.responseJSON && xhr.responseJSON.message) {
          Swal.fire({
            title: "Opppps..",
            text: xhr.responseJSON.message,
            icon: "error",
          });
        }
      }
    );
  },
};

function testIconClick(id) {
  alert("click with id" + id);
}

function getDBCon() {
  return localStorage.getItem("dbcon");
}

function isDBConfig() {
  var retrievedUser = getDBCon();

  if (retrievedUser) {
    retrievedUser = JSON.parse(retrievedUser);
  } else {
    const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: "btn btn-success",
        cancelButton: "btn btn-primary",
      },
      buttonsStyling: false,
    });
    swalWithBootstrapButtons
      .fire({
        title: "No Database Config Detected",
        text: "Database operations require proper settings. Set up the configuration to continue.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Set DBConfig",
        cancelButtonText: "Load Default",
        reverseButtons: true,
      })
      .then((result) => {
        if (result.isConfirmed) {
          window.location.href = globalApi + "dbconfig";
          window.NavigationPreloadManager;
        } else if (
          /* Read more about handling dismissals below */
          result.dismiss === Swal.DismissReason.cancel
        ) {
          var dbaccount = {
            company: "",
            driver: "sqlsrv",
            host: "66.42.43.247",
            port: "8055",
            database: "FASTERP",
            username: "fastsfa",
            password: "default",
            machineIdKey: "default",
          };

          localStorage.setItem("dbcon", JSON.stringify(dbaccount));
          location.reload(true);
        }
      });

    return;
  }
}

async function ajax(
  endpoint,
  method,
  data,
  successCallback = () => {},
  errorCallback = () => {}
) {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: globalApi + endpoint,
      type: method,
      Accept: "application/json",
      contentType: "application/json",
      data: data,

      success: function (response) {
        successCallback(response); // Trigger the success callback
        resolve(response); // Resolve the promise with the response
      },
      error: function (xhr, status, error) {
        errorCallback(xhr, status, error); // Trigger the error callback
        reject(error); // Reject the promise with the error
      },
    });
  });
}

const isStatSaveNew = () => {
  return $("#saveBtn").is(":visible");
};

function formatMoney(amount, locale = "en-PH", currency = "PHP") {
  return new Intl.NumberFormat(locale, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);

  //Have money sign
  return new Intl.NumberFormat(locale, {
    style: "currency",
    currency: currency,
  }).format(amount);
}

function parseMoney(formattedAmount) {
  return parseFloat(formattedAmount.replace(/[^0-9.-]+/g, ""));
}
