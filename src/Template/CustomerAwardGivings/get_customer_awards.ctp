   <?php if($customer_awards){?>
       <table class="table table-bordered">
           <tr>
               <td>Sl No:</td>
               <td>Award Name</td>
               <td>Program Name</td>
               <td>Program Period Start Date</td>
               <td>Program Period End Date</td>
               <td>Cash Amount</td>
               <td>Remaining Amount</td>
               <td>Action</td>
           </tr>
           <?php foreach($customer_awards as $key=>$row):?>
               <?php $key++?>
               <tr>
                   <td><?=$key?></td>
                   <td><?=$row['award']['name']?></td>
                   <td><?=$row['customer_offer']['programe_name']?></td>
                   <td><?=date('d-M-Y',$row['offer_period_start'])?></td>
                   <td><?=date('d-M-Y',$row['offer_period_end'])?></td>
                   <td><?=$row['amount']?></td>
                   <td><?=$row['remaining_amount']?></td>
                   <td><?=     $this->Html->link('<button class="btn btn-info " type="button">Deliver Award</button>', ['action' => 'deliverAward', $row['id']
                       ], ['escapeTitle' => false, 'title' => 'Deliver Award']);?><br/>

                       <button data-row-id="<?=$row['id']?>" type="button" class="btn btn-primary adjustment" data-toggle="modal" data-target="#myModal">
                          Adjustment
                       </button>



                   </td>
               </tr>
           <?php endforeach;?>
       </table>

   <?php }?>
