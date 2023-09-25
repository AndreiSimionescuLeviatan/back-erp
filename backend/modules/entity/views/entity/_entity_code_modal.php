<?php

use yii\web\View;

?>

<style>

    .row-header {
        margin: 1px 0 0 0;
        background-color: aliceblue;
    }

    .modal-entity {
        position: absolute;
        background-color: white;
        width: 500px;
        max-height: 100px;
        z-index: 10000;
        overflow-y: auto;
        border-style: groove;
    }

    .hover-pointer-entity:hover {
        cursor: pointer;
    }

    .entity-close-window {
        max-height: 25px;
        position: sticky;
        top: 0;
    }

</style>

<div class="modal-entity p-0 d-none" id="entity-delete-modal">
    <div class="row row-header">
        <div class="col-11 d-flex justify-content-start">
            <span id="code_item"></span>
        </div>
        <div class="col-1 pr-0 d-flex justify-content-end" style="max-width: 10%;">
            <button class="btn btn-sm pt-0 pb-0 entity-close-window btn-default fas fa-times" style="font-weight:600;">
            </button>
        </div>
    </div>
</div>


<?php
$this->registerJs(
    "initModal();",
    View::POS_READY,
    'init-modal-handler'
);
?>

<script>

    var selectedElement;

    function setCodeEntityReplace(message)
    {
        document.getElementById("code_item").innerHTML = `${message}`;
    }

    function initModal()
    {
        $('.hover-pointer-entity').on("click", function () {
            selectedElement = $(this);
            if (selectedElement.attr('onclick') === undefined) {
                closeModalWindow();
            } else {
                setModalWindowPosition();
            }
        });

        $('.entity-close-window').on("click", function () {
            closeModalWindow();
        });

        $('div').on('scroll', function () {
            closeModalWindowScroll();
        });

        $(document).scroll(function () {
            closeModalWindowScroll();
        })

        $(document).on("click", function () {
            if (
                event === undefined
                || event.target === undefined
            ) {
                return;
            }

            if (
                event.target
                && typeof event.target.matches === 'function'
                && !event.target.matches('.hover-pointer-entity, .modal-entity, .modal-entity *')
            ) {
                closeModalWindow();
            }
        });
    }

    function setModalWindowPosition()
    {
        if (selectedElement === undefined) {
            closeModalWindow();
            return;
        }

        let position = selectedElement.offset();
        let leftPos = parseFloat(position.left) + parseFloat(selectedElement.outerWidth()) + 5;
        let topPos = parseFloat(position.top) - 2;

        $('.modal-entity').removeClass('d-none');
        $('.modal-entity').attr('style', 'top: ' + topPos + 'px; left: ' + leftPos + 'px;');
    }

    function closeModalWindowScroll()
    {
        let element = event.srcElement.className;
        if (
            element === undefined
            || !element.includes('modal-entity')
        ) {
            closeModalWindow();
        }
    }

    function closeModalWindow()
    {
        $('.modal-entity').addClass('d-none');
        selectedElement = undefined;
    }

</script>