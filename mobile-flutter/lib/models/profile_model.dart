class Profile {
  final String namaInstansi;
  final String alamat;
  final String telp;
  final String gambarLogo;
  final String colorPalette;

  Profile({
    required this.namaInstansi,
    required this.alamat,
    required this.telp,
    required this.gambarLogo,
    required this.colorPalette,
  });

  factory Profile.fromJson(Map<String, dynamic> json) {
    return Profile(
      namaInstansi: json['nama_instansi'],
      alamat: json['alamat'],
      telp: json['telp'],
      gambarLogo: json['gambar_logo'],
      colorPalette: json['color_palette'],
    );
  }
}
