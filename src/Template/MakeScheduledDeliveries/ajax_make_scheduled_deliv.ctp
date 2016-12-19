<?php
$status = \Cake\Core\Configure::read('status_options');
use Cake\Routing\Router;
use App\View\Helper\SystemHelper;


?>
<br/>
<div class="row">
    <div class="col-sm-offset-3 col-sm-6">
        <table class="table table-bordered">
            <tr>
                <td>PI-No:</td>
                <td><?=$do_object->serial_no?></td>
            </tr>
            <tr>
                <td>Requested By:</td>
                <td><?=$do_object->requested_by_name?></td>
            </tr>
            <tr>
                <td>Date:</td>
                <td><?php echo date('d-M-Y',$do_object->date)?></td>
            </tr>
            <tr>
                <td>Delivery Status:</td>
                <td><?php if($do_object->delivery_status==3){echo "<span class='label label-success'>Not Delivered</span>";}
                    else{echo "<span class='label label-danger'>Delivered</span>";}?></td>
            </tr>
        </table>
    </div>

   <div class="col-sm-12">
       <table class="table table-bordered">
           <tr>
               <td>Item Name</td>
               <td>Unit Name</td>
               <td>Approved Quantity</td>
               <?php if($do_object->delivery_status==3){echo " <td>Current Stock</td>";} ?>

           </tr>
           <?php foreach($do_object_items as $row):?>
               <tr>
                   <td><?= $row['item_name']?></td>
                   <td><?= $row['unit_name']?></td>
                   <td><?= $row['approved_quantity']?></td>
                   <?php if($do_object->delivery_status==3){echo "<td>".$row['current_stock']."</td>";} ?>
               </tr>
           <?php endforeach;?>
       </table>

       <form class="form-horizontal" method="post"   action="<?php echo Router::url('/', true); ?>MakeScheduledDeliveries/makeScheduledDeliv/<?= $do_event_id.'/'.$ds_tbl_id.'/'.$do_object_id ?>" enctype="multipart/form-data">


           <div class="form-group">
               <div class="col-sm-offset-5 col-sm-3">
                   <button type="submit" class="btn btn-primary btn-lg">Deliver</button>
               </div>
           </div>
       </form>



       <table class="table table-bordered">
           <thead>
           <tr>
               <td>SL</td>
               <td>Delivering Warehouse</td>
               <td>Receiving Warehouse</td>
               <td>Serial Number</td>
               <td>Date</td>
               <td>Action</td>
           </tr>
           </thead>
           <tbody>
           <?php foreach($ddoss as $key=>$row):?>
               <tr>
                   <td><?= $key+1?></td>
                   <td><?= $row['do_delivering_warehouse_name']?></td>
                   <td><?= $row['do_receiving_warehouse_name']?></td>
                   <td><?= $row['do_ds_serial_number']?></td>
                   <td><?= date('d-M-Y',$row['date'])?></td>
                   <td><a href="<?php echo Router::url('/', true); ?>MakeScheduledDeliveries/viewDosItems/<?= $row['id'] ?>" class="btn btn-success" target="_blank">View</a> </td>
               </tr>
           <?php endforeach;?>
           </tbody>
       </table>
   </div>
</div>