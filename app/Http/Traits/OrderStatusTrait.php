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
     * @param  string  $downloads
     * @param  string  $companyId
     * @param  string  $orderNo
     * @return \Illuminate\Http\Response
     */
    public function orderLabels($status, $downloads, $companyId, $orderNo) 
    {
    	if( $status === 'pending' ) {
    		$orderStatus = '<span class="badge badge-warning">'.ucfirst($status).'</span>';
            $orderDownload = '<span class="badge badge-secondary">No Link</span>';
    	}
    	elseif( $status === 'editable' ) {
    		$orderStatus = '<span class="badge badge-dark">'.ucfirst($status).'</span>';
            $orderDownload = '<span class="badge badge-secondary">No Link</span>';
    	}
    	elseif( $status === 'shipped' ) {
    		$orderStatus = '<span class="badge badge-info">'.ucfirst($status).'</span>';
            $orderDownload = '<button type="button" class="btn btn-outline-primary btn-sm download" data-companyid="'.$companyId.'" data-orderno="'.$orderNo.'"><i class="fas fa-download"></i></button>';
    	}
    	elseif( $status === 'payout' ) {
    		$orderStatus = '<span class="badge badge-success">'.ucfirst($status).'</span>';
            $orderDownload = '<span class="badge badge-secondary">No Link</span>';
    	}
    	elseif( $status === 'cancelled' ) {
    		$orderStatus = '<span class="badge badge-danger">'.ucfirst($status).'</span>';
            $orderDownload = '<span class="badge badge-secondary">No Link</span>';
    	}
    	else {
    		$orderStatus = '<span class="badge badge-secondary">No Status</span>';
            $orderDownload = '<span class="badge badge-secondary">No Link</span>';
    	}

        if($downloads === 'downloads') {
            return $orderDownload;
        }
        else {
            return $orderStatus;
        }
        
    }
}

?>