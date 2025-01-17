<?php
// printA($config);
// printA($kelas);
?>
<div class="row mt-5">
	<div class="col-md-12">
		<div class="panel-body bio-graph-info"></div>
		<div class="card">
			<div class="card-header text-center">
				<h1 class="font-weight-bold">Angket Siswa</h1>
			</div>
			<div class="card-body">
				<form action="<?php echo base_url('sosiometrisiswa/angketSiswa'); ?>" class="form-horizontal" method="post">
					<input type="hidden" name="id_sosiometri" value="<?php echo $config['id']; ?>">
					<div class="form-group">
						<label for="kelas" class="col-lg-12 control-label">Kelas</label>
						<div class="col-lg-12">
							<select name="kelas" id="kelas" class="form-control" id="kelas" required>
								<option value="">--PILIH KELAS KAMU--</option>
								<?php
								if ($kelas) {
									foreach ($kelas as $row) {
								?>
										<option value="<?php echo $row['id']; ?>"><?php echo $row['kelas']; ?></option>
								<?php
									}
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label for="nama" class="col-lg-12 control-label">Nama</label>
						<div class="col-lg-12">
							<select name="id_siswa" id="id_siswa" class="form-control" required>
								<option value="">--PILIH NAMA KAMU--</option>
							</select>
						</div>
					</div>
					<div class="form-group">
						<p class="ml-4 mt-5">
							<strong><?php echo $config ? $config['pertanyaan'] : ''; ?></strong>
						</p>
					</div>
					<div class="form-group" id="pilihanGroup">
						<?php
						if ($config) {
							for ($i = 1; $i <= $config['jumlah_pilihan']; $i++) {
						?>
								<label for="pilihan" class="col-lg-12 control-label">Pilihan <?php echo $i; ?></label>
								<div class="col-lg-12">
									<select name="pilihan[]" id="pilihan<?php echo $i; ?>" class="form-control" disabled required></select>
								</div>
						<?php
							}
						}
						?>
					</div>
					<div class="form-group">
						<label for="pilihan" class="col-lg-12 control-label">Siapakah orang yang paling tidak sesuai dengan pertanyaan diatas?</label>
						<div class="col-lg-12">
							<select name="pilihan_negatif" id="pilihan_negatif" class="form-control" disabled></select>
						</div>
					</div>
					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="submit" class="btn btn-success">Save</button>
							<!-- <button type="button" class="btn btn-default">Cancel</button> -->
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		let pilihan = <?php echo $config['jumlah_pilihan']; ?>;
		let siswaChosen = [];
		let arrPilihan = [];
		let indexCounter = 0;

		$("#kelas").on("change", function() {
			let idKelas = $(this).val()
			// console.log(idKelas)

			$.ajax({
				type: 'GET',
				url: "<?php echo base_url('sosiometrisiswa/getSiswa') ?>" + '/' + idKelas,
				cache: false,
				success: function(resp) {
					console.log(resp)
					var siswaOptions = '';

					if (resp) {
						if (resp.data) {
							siswaOptions += '<option value="">--PILIH NAMA KAMU--</option>'
							$.each(resp.data, function(i, row) {
								siswaOptions += '<option value=' + row.id + '>' + row.nama + '</option>'
							})

						}
					}

					$("#id_siswa").html(siswaOptions)
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error(textStatus)
				}
			})
		})

		$("#id_siswa").on("change", function() {
			// Do reset 
			doReset()

			$('select[name="pilihan[]"]').each(function() {
				arrPilihan.push($(this).attr('id'))
			})

			// console.log('Options', arrPilihan)

			// Save siswa id to siswaChosen
			siswaChosen.push($(this).val())
			console.log('Student chosen', siswaChosen)

			$.each(arrPilihan, function(i, v) {

				selectOnChange(v)

				return false;
			})

		})

		function selectOnChange(selector) {

			ajaxGetSiswaNotIn(selector);

			$('#' + selector).attr('disabled', false)

			$('#' + selector).on("change", function() {

				var selectorChange = arrPilihan.indexOf(selector) + 1
				console.log('Index', selectorChange)

				if (siswaChosen[selectorChange] !== undefined) {
					siswaChosen[selectorChange] = $(this).val()
				} else {
					siswaChosen.push($(this).val())
				}

				// console.log(selector)
				console.log('Student chosen', siswaChosen)

				// indexCounter +1
				// indexCounter++;


				// ajaxGetSiswaNotIn(arrPilihan[indexCounter])

				// $('#' + arrPilihan[indexCounter]).attr('disabled', false)

				// selectOnChange(arrPilihan[indexCounter])

				$('#pilihan_negatif').attr('disabled', false)

				ajaxGetSiswaNotIn('pilihan_negatif')

				selectOnChange(arrPilihan[selectorChange])
			})
		}

		function ajaxGetSiswaNotIn(selector) {
			$.ajax({
				type: 'POST',
				data: {
					id_kelas: $('#kelas').val(),
					id_siswa: siswaChosen
				},
				url: "<?php echo base_url('sosiometrisiswa/getSiswaNotIn'); ?>",
				cache: false,
				success: function(resp) {
					// console.log('Data siswa', resp)

					if (resp.success) {
						var optionTemp = selector == 'pilihan_negatif' ? '<option value="">--TIDAK ADA--</option>' : '<option value="">--PILIH NAMA SISWA--</option>';

						$.each(resp.data, function(i, v) {
							optionTemp += '<option value="' + v.id + '">' + v.nama + '</option>'
						})

						$('#' + selector).html(optionTemp)
					}
				},
				error: function(jqXHR, textStatus, errorThrown) {
					console.error(jqXHR, textStatus, errorThrown)
				}
			})
		}

		function doReset() {
			// Reset siswaChosen
			siswaChosen = []

			// Reset index counter
			indexCounter = 0

			// Reset pilihan array options
			arrPilihan = []

			$('select[name="pilihan[]"]').each(function() {
				// Reset pilihan option values
				$('#' + $(this).attr('id')).html('')
				// Reset pilihan option disabled to be true
				$('#' + $(this).attr('id')).attr('disabled', true)

				// Reset pilihan option on change event
				$('#' + $(this).attr('id')).off('change')

				// Reset pilihan_negatif option
				$('#pilihan_negatif').html('')

				// Reset pilihan_negatif disabled to be true
				$('#pilihan_negatif').attr('disabled', true)

				// Reset pilihan_negatif on change event
				$('#pilihan_negatif').off('change')
			})
		}
	})
</script>
