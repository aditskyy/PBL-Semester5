import 'package:flutter/material.dart';
import 'pages/display_page.dart';

void main() {
  runApp(const DisplayApp());
}

class DisplayApp extends StatelessWidget {
  const DisplayApp({super.key});

  @override
  Widget build(BuildContext context) {
    return const MaterialApp(
      debugShowCheckedModeBanner: false,
      home: DisplayPage(),
    );
  }
}
