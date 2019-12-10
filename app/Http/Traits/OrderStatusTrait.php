<?php

namespace App\Http\Traits;

trait OrderStatusTrait {

    /**
     * Order api status
     * @return \Illuminate\Http\Response
     */
	public function orderStatuses()
	{
		$orderStatuses = [ 
			'pending' => ucfirst('pending'), 
			'editable' => ucfirst('editable'),
			'shipped' => ucfirst('shipped'),
			'payout' => ucfirst('payout'),
			'cancelled' => ucfirst('cancelled'),
		];

        return $orderStatuses;
	}

	/**
     * Creating order status labels.
     *
     * @param  string  $status
     * @return \Illuminate\Http\Response
     */
    public function orderLabels($status) 
    {
    	if( $status === 'pending' ) {
    		$orderStatus = '<span class="badge badge-warning">'.ucfirst($status).'</span>';
    	}
    	elseif( $status === 'editable' ) {
    		$orderStatus = '<span class="badge badge-dark">'.ucfirst($status).'</span>';
    	}
    	elseif( $status === 'shipped' ) {
    		$orderStatus = '<span class="badge badge-info">'.ucfirst($status).'</span>';
    	}
    	elseif( $status === 'payout' ) {
    		$orderStatus = '<span class="badge badge-success">'.ucfirst($status).'</span>';
    	}
    	elseif( $status === 'cancelled' ) {
    		$orderStatus = '<span class="badge badge-danger">'.ucfirst($status).'</span>';
    	}
    	else {
    		$orderStatus = '<span class="badge badge-secondary">No Status</span>';
    	}
        
        return $orderStatus;
    }
}

?>