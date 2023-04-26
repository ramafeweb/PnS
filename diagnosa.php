<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Sipaba</title>
  <link rel="stylesheet" href="css/proses.css">
</head>

<body>
  <h1 style="text-align: center;">Sistem Pakar Penentuan Penyakit Bayi</h1>

  <div class="card">
    <h2>Silakan Pilih Gejala yang Dirasakan</h2>
    <form method="post" action="hasil.php">
      <?php
      include 'koneksi.php';
      // menampilkan daftar gejala
      $sql = "SELECT * FROM ds_evidences";
      $result = $db->query($sql);
      while ($row = $result->fetch_object()) {
        echo "
        <label>
          <input type='checkbox' name='evidence[]' value='{$row->id}'> {$row->code} {$row->name}
        </label>
        <br>
        ";
      }
      ?>
      <input type="submit" value="Diagnosa">
    </form>
  </div>
</body>

</html>