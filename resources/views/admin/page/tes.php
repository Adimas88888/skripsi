<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include "base.php";

/*$query->execute("select *, date_format(tanggal, '%Y%m%d') as tgl, r.kd_rekomendasi as kd_rekom, r.nomor, r.tanggal,
					date_format(tgl_surat_penolakan, '%Y%m%d') as tgl_tolak 
				from 
					e_licensing.rekomendasi r, e_licensing.rekomendasi_aju ra, e_licensing.mst_rekomendasi m, nk n 
				where r.kd_rekomendasi=m.kd_rekomendasi and r.id_rekomendasi_aju=ra.id_rekomendasi_aju 
					and r.id_nk=n.id_nk 
				    and posisi in ('Es1 - Tolak', 'Es1 - Setuju')
				    and tgl_aju > '2021-09-21'
				    and r.id_rekomendasi = '11209846' 
					and r.kd_rekomendasi in (176, 177, 178, 179, 182) 
					#and tgl_kirim is null");*/
					
$query->execute("select *, date_format(tanggal, '%Y%m%d') as tgl, r.kd_rekomendasi as kd_rekom, r.nomor, r.tanggal, ekspor, 
					date_format(tgl_surat_penolakan, '%Y%m%d') as tgl_tolak 
				from 
					e_licensing.rekomendasi r, e_licensing.rekomendasi_aju ra, e_licensing.mst_rekomendasi m, nk n 
				where r.kd_rekomendasi=m.kd_rekomendasi and r.id_rekomendasi_aju=ra.id_rekomendasi_aju 
					and r.id_nk=n.id_nk 
				    and posisi in ('Es1 - Tolak', 'Es1 - Setuju')
				    and year(tgl_aju) >= '2022'
				    #and r.id_rekomendasi = '11205776' 
					and r.kd_rekomendasi in (176, 177, 178, 179, 182, 184, 185, 186, 188, 
					193, 194, 195, 199, 200, 201, 202, 203, 207, 208, 209, 210, 211, 212, 213, 214, 215, 216, 
					217, 218, 219, 220, 221, 222, 223, 224, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 
					235, 236, 237, 240, 241, 242, 243, 244, 245, 246, 247, 248, 255, 256, 260) 
					and tgl_kirim is null");
					
while ($rekom = $query->fetch()) {
	$query2->execute("select * from e_licensing.nk 
	where id_nk=".$rekom["id_nk"]);
	$nk = $query2->fetch();
	
	$query2->execute("select * from e_licensing.nk_rincian  
	where id_rekomendasi=".$rekom["id_rekomendasi"]);
	$nk_rinci = $query2->fetch();
	
	if ($rekom["posisi"] == "Es1 - Tolak") {
	    $no_rekom = $rekom["no_surat_penolakan"];
	    $tanggal = $rekom["tgl_surat_penolakan"];
		$status = "tolak";
		$keterangan = $rekom["alasan_penolakan"];
		$url = "";
		$kd_keputusan = "T";
	}else {
	    $no_rekom = "";
	    $tanggal = "";
	    $status = "terima";
	    $keterangan = "";
	    $url = "";
	    $kd_keputusan = "P";
	  
	    if(isset($nk_rinci["nomor"])&&$nk_rinci["nomor"]&&(!in_array($rekom["kd_rekom"], array("228","243","242","213","246")))){
	    	if($rekom["kd_rekom"]=="179"){
	    		$no_rekom = $nk_rinci["nomor"]." & ".$rekom["no_surat"];
	    	}else{
	    		$no_rekom = $nk_rinci["nomor"];
	    	}	 
	    	 
	    	$tanggal = $nk_rinci["tanggal"];
	    	//$url = "https://kemenperin.go.id/nk/".encid($rekom["id_rekomendasi"]);
	    	$url = "";
	    }else{ 
	    	$no_rekom = $rekom["nomor"];
	    	$tanggal = $rekom["tanggal"];
	    	$url = $rekom["url_softcopy"];
	    	//$url = ""; //saat ini dikosongkan dahulu
	    } 
	}
	
	$header = array(
	  "noAju"=>$rekom["no_surat"],
	  "seriPerubahan"=>$nk["no_seri_aju"],
	  "tglAju"=>$rekom["tgl_surat"],
	  "kdKeputusan"=>$kd_keputusan,
	  "npwp"=>$nk["npwp"],
	  "nib"=>$nk["nib"],
	  "kdIjin"=>$rekom["kd_insw"],
	  "urlKep"=>$url,
	  "noKep"=>$no_rekom,
	  "tglKep"=>$tanggal,
	  "keterangan"=>$keterangan
	);
	
	$komoditas = array();
	
	if($rekom["ekspor"]!=1){//NK Impor
		$query2->execute("select * from bhnbaku_rencana_impor 
		where id_nk='".$rekom["id_nk"]."' and kd_ijin = '".$rekom["kd_insw"]."'");
		
		while ($row = $query2->fetch()) {
		  if($status == "tolak"){
		  	$jumlah = 0;
		  }else{
		  	$jumlah = $row["jumlah_izin"];
		  }	
		  
		  $komoditas["rencanaImpor"][] = array(
		    "tahun"=>$row["tahun"],
		    "noUrut"=>$row["no_urut"],
		    "negAsal"=>explode(",",$row["negara_asal"]),
		    "negMuat"=>explode(",",$row["negara_muat"]),
		    "pelMuat"=>explode(",",$row["kd_pelabuhan_muat"]),
		    "pelBongkar"=>explode(",",$row["kd_pelabuhan"]),
		    "jmlProduk"=>$jumlah,
		    //"jmlProduk"=>$row["jumlah"],
		    "satuan"=>$row["kd_satuan"],
		    "hsCode"=>$row["kd_hs"],
		    "kdIjin"=>$row["kd_ijin"],
		    "urBarang"=>$row["uraian"],  
		  );
		}
	
	
		$query2->execute("select * from bhnbaku_rencana_lokal  
		where id_nk='".$rekom["id_nk"]."' and kd_ijin = '".$rekom["kd_insw"]."'");
		
		while ($row = $query2->fetch()) {
		  $komoditas["rencanaLokal"][] = array(
		    "tahun"=>$row["tahun"],
		    "noUrut"=>$row["no_urut"],
		    "jmlProduk"=>$row["jumlah_izin"],
		    //"jmlProduk"=>$row["jumlah"],
		    "satuan"=>$row["kd_satuan"],
		    "hsCode"=>$row["kd_hs"],
		    "kdIjin"=>$row["kd_ijin"],
		    "urBarang"=>$row["uraian"],  
		  );
		}
	}else{//NK ekspor
		$query2->execute("select * from distribusi 
		where id_nk='".$rekom["id_nk"]."' and kd_ijin = '".$rekom["kd_insw"]."' order by no_urut");
		
		while ($row = $query2->fetch()) {
		  $query3->execute("select * from distribusi_ekspor  
				where id_distribusi='".$row["id_distribusi"]."'");
		  
		  $data_ekspor = array();
		  while ($ekspor = $query3->fetch()) {
		  	if($status == "tolak"){
			  	$jumlah = 0;
			}else{
			  	$jumlah = $ekspor["jumlah_izin"];
			}	
		  
		  	$data_ekspor[] = array(
				"satuan"=>$ekspor["kd_satuan"],
	            "pelMuat"=>explode(",",$ekspor["kd_pelabuhan"]),
	            "jmlProduk"=>$jumlah,
			    "negTujuan"=>explode(",",$ekspor["negara_tujuan"]),
			);
		  }
		  
		  $komoditas[] = array(
		    "hsCode"=>$row["kd_hs"],
		    "kdIjin"=>$row["kd_ijin"],
		    "noUrutBarang"=>$row["no_urut"],
		    //"satuan"=>$row["kd_satuan"],
		    //"asalData"=>"",
		    "urBarang"=>$row["uraian"],
		    //"jmlProduk"=> $row["jumlah"],
		    "jnsProduk"=> $row["jenis_produk"],
	        "spesifikasi"=> $row["spesifikasi"],
	        "ur_asalData"=> $row["asal_data"],
	        "ekspor"=>$data_ekspor
		  );
		}
	}
	
	$proses = array();
	
	
	if($rekom["posisi"] == "Es1 - Tolak"){
		$proses["kdStatus"] = "300";
		$proses["urStatus"] = "Penolakan Permohonan";
		$proses["wkStatus"] = $rekom["tgl_tolak"];
	}else{
		$proses["kdStatus"] = "600";
		$proses["urStatus"] = "Penerbitan Perijinan/Rekomendasi";
		$proses["wkStatus"] = $rekom["tanggal"];	
	}
	
	if($rekom["ekspor"]!=1){//NK Impor
		$data = array("header"=>$header, "bahanBaku"=>$komoditas,
		"status"=>$proses);//NK Ekspor
	}else{
		$data = array("header"=>$header, "distribusi"=>$komoditas,
		"status"=>$proses);
	}
	
	$data = json_encode($data, 1);
	
	echo "<pre>";
	print_r($data);
	echo "</pre><br><br>";
	
	//$curl = curl_init("https://api.insw.go.id/kemenperin-dev/nk/sendResponse");
	$curl = curl_init("https://api.insw.go.id/kemenperin/nk/sendResponse");
	
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	
	curl_setopt($curl, CURLOPT_HTTPHEADER,
	  array(
	    "insw-key: 8P9gURvjNr5Bx2HmAIW28xF0QOiUP7W8",
	    "Content-Type: application/json"
	  )
	);
	
	$result = curl_exec($curl);
	
	if ($result === false) {
	   echo 'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
	   die;
	}
	
	echo $result;
	
	$result_json = $result;
	$result = json_decode($result, 1);
	
	if(isset($result["kode"])){
		$kode = $result["kode"];
	}else{
		$kode = $result["code"];
	}
	
	$status = $result["keterangan"]." - ".
	"(".$result["data"]["kode"]." - ".$result["data"]["keterangan"].")";
	
	$query2->execute("insert into e_licensing.nk_log_kirim (id_rekomendasi, ws_name,
	kode, status, data, response, tanggal) values
	('".$rekom["id_rekomendasi"]."', 'send-response-keputusan', '".$kode."', '".addslashes($status)."',
	'".addslashes($data)."', '".addslashes($result_json)."', now())");
	
	if($kode == 200)
		$query2->execute("update e_licensing.rekomendasi
	  			set tgl_kirim=now()
	  			where id_rekomendasi=".$rekom["id_rekomendasi"]);
	
	curl_close($curl);
}

function encid($id) {
  $ENCID_KEY = "av4dakadaBRA!!!";
  
  $s = strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,
  md5($ENCID_KEY), serialize($id), MCRYPT_MODE_CBC,
  md5(md5($ENCID_KEY)))), '+/=', '-_,');

  return $s;
}
?>