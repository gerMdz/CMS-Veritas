import $ from 'jquery';
import 'select2';                       // globally assign select2 fn to $ element
import 'select2/dist/css/select2.css';  // optional if you have css loader

$(function () {
    $('.select2-enable').select2({
        placeholder: 'Seleccione ... ',
        dropdownParent: $("#modalPlantillaWeb")
    });

    console.log('select2')
});

