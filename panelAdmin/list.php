<?php
    include_once '../_includes.php';
    checksession('index.php');
    $tabla = decrypt( $_GET['ta']);//table
    $titulo = decrypt( $_GET['ti']);//title
    $parametro = isset($_GET['parametro'])?$_GET['parametro']:'';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>{ - <?php echo printo('MSJ_PAGINA_TITULO') ?> | List of <?php echo $titulo; ?> - }</title>
    <!--========================== INCLUDE HEADER =======================-->                           
    <?php include 'include-head.php'; ?>
    <link rel="stylesheet" href="../res/fancybox/jquery.fancybox.css" type="text/css" media="screen" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.5/css/jquery.dataTables.min.css" type="text/css" media="screen"/>
    <style>
        /***
        .formTableFilter {
          text-align: right;
        }
        .formTableFilter label {
          font-weight: bold;
        }
        .formTableFilter input {
            width: 100%;
        }
        /**/
    </style>
</head>
<body lang="<?php echo $global_language; ?>">
    <input type="hidden" value="<?php echo $tabla;?>" id="tabla" name="tabla" />
    <input type="hidden" value="<?php echo $parametro;?>" id="parametro" name="parametro" />
    <div class="container">
        <?php include 'menu.php' ?>
        <h3>List of  <?php echo $titulo; ?></h3>
        <div id="div-listado">
            
        </div>
        <div id="div-lote" style="display: none;">
            <legend>Proceso por lote</legend>
            <div class="pull-right">
                <button class="btn btn-default" onclick="seleccionar_visibles(true);">Select Visibles</button>
                <button class="btn btn-default" onclick="seleccionar_visibles(false);">Deselect isibles</button>
                <button class="btn btn-danger" onclick="eliminar_seleccion();">Delete Selected</button>
            </div>
        </div>
        <div id="statusRegistro"  class="modal fade">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 id="tituloStatus" class="modal-title"></h4>
                    </div>
                    <form onsubmit="event.preventDefault();statusRegistro();">
                        <div class="modal-body">
                            <div id="modal-body-statusRegistro"></div>                        
                            <div class="clearfix"></div>
                            <div id="responseStatus"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-success" onsubmit="return false;" id="btnStatusRegistro" >OK</button>
                        </div>
                    </form>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div>
    <?php include 'include-js.php'; ?>
    </body>   
        <!-- Add fancyBox -->        
        <script type="text/javascript" src="../res/fancybox/jquery.fancybox.pack.js"></script><!---->            
        <script type="text/javascript" src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js"></script>
        <script src="js/list.js" ></script>
</html>
