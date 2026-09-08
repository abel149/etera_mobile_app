import 'dart:typed_data';
import 'package:image/image.dart' as img;
import 'package:flutter/material.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import '../../config/theme.dart';
import '../../models/proforma.dart';
import '../../services/api_service.dart';

/// Clean printable / downloadable invoice page for a single shop/garage quote.
/// Mirrors the web app's openPrintPage() functionality.
class ProformaInvoicePrintScreen extends StatefulWidget {
  final ProformaApplication application;
  final List<ProformaPartItem> parts;
  final String fileNumber;
  final String vehicleInfo; // e.g. "Toyota Corolla 2019"

  const ProformaInvoicePrintScreen({
    super.key,
    required this.application,
    required this.parts,
    required this.fileNumber,
    required this.vehicleInfo,
  });

  @override
  State<ProformaInvoicePrintScreen> createState() =>
      _ProformaInvoicePrintScreenState();
}

class _ProformaInvoicePrintScreenState
    extends State<ProformaInvoicePrintScreen> {
  bool _generating = false;

  /// Re-encodes any image format (JPEG/PNG/GIF/WEBP/TIFF/BMP/etc.) to PNG
  /// using the pure-Dart `image` package — works on all formats including TIFF.
  Uint8List? _normalizeImageBytes(Uint8List raw) {
    try {
      debugPrint('[InvoicePDF] Decoding ${raw.length} bytes with image package...');
      final decoded = img.decodeImage(raw);
      if (decoded != null) {
        debugPrint('[InvoicePDF] Decoded successfully: ${decoded.width}x${decoded.height}');
        final png = Uint8List.fromList(img.encodePng(decoded));
        debugPrint('[InvoicePDF] Encoded to PNG: ${png.length} bytes');
        return png;
      } else {
        debugPrint('[InvoicePDF] decodeImage returned null');
      }
    } catch (e, st) {
      debugPrint('[InvoicePDF] Image normalize failed: $e');
      debugPrint('[InvoicePDF] Stack trace: $st');
    }
    return null;
  }

  Future<Uint8List?> _loadImageBytes(String url) async {
    return ApiService.loadImageBytes(url);
  }

  bool _isSvgBytes(Uint8List bytes) {
    final headLength = bytes.length < 500 ? bytes.length : 500;
    final head = String.fromCharCodes(bytes.take(headLength)).trimLeft().toLowerCase();
    return head.startsWith('<svg') || head.contains('<svg');
  }

  pw.Widget _stampCircleFromSvg(String svg, double size,
      {double opacity = 1.0, double rotation = 0.0}) {
    pw.Widget w = pw.Container(
      width: size,
      height: size,
      decoration: pw.BoxDecoration(
        shape: pw.BoxShape.circle,
        color: PdfColors.teal50,
        border: pw.Border.all(color: PdfColors.teal700, width: 2),
      ),
      child: pw.ClipOval(
        child: pw.SvgImage(svg: svg),
      ),
    );
    if (rotation != 0.0) w = pw.Transform.rotate(angle: rotation, child: w);
    if (opacity < 1.0) w = pw.Opacity(opacity: opacity, child: w);
    return w;
  }

  /// Helper — builds a circular stamp container using DecorationImage.
  pw.Widget _stampCircle(pw.MemoryImage img, double size,
      {double opacity = 1.0, double rotation = 0.0}) {
    pw.Widget w = pw.Container(
      width: size,
      height: size,
      decoration: pw.BoxDecoration(
        shape: pw.BoxShape.circle,
        color: PdfColors.teal50,
        border: pw.Border.all(color: PdfColors.teal700, width: 2),
        image: pw.DecorationImage(image: img, fit: pw.BoxFit.cover),
      ),
    );
    if (rotation != 0.0) w = pw.Transform.rotate(angle: rotation, child: w);
    if (opacity < 1.0) w = pw.Opacity(opacity: opacity, child: w);
    return w;
  }

  Future<Uint8List> _buildPdf() async {
    final pdf = pw.Document();
    final ap = widget.application;
    final applicant = ap.applicant;
    final isShop = ap.from == 'shop';
    final typeLabel = isShop ? 'Spare Part Shop' : 'Garage';

    // Load stamp image — log URL and byte count so we can debug
    pw.MemoryImage? stampImg;
    String? stampSvg;
    final stampUrl = applicant.stampImageUrl;
    debugPrint('[InvoicePDF] stampUrl = $stampUrl');
    if (stampUrl != null) {
      final bytes = await _loadImageBytes(stampUrl);
      debugPrint('[InvoicePDF] stampBytes = ${bytes?.length ?? "null"}');
      if (bytes != null) {
        if (_isSvgBytes(bytes)) {
          stampSvg = String.fromCharCodes(bytes);
          debugPrint('[InvoicePDF] stampSvgBytes = ${bytes.length}');
        } else {
          final png = _normalizeImageBytes(bytes);
          debugPrint('[InvoicePDF] stampPngBytes = ${png?.length ?? "null"}');
          if (png != null) {
            stampImg = pw.MemoryImage(png);
          } else {
            // Fallback: try raw bytes directly (pw.MemoryImage might detect format)
            debugPrint('[InvoicePDF] Trying raw bytes directly...');
            try {
              stampImg = pw.MemoryImage(bytes);
              debugPrint('[InvoicePDF] Raw bytes accepted by pw.MemoryImage');
            } catch (e) {
              debugPrint('[InvoicePDF] Raw bytes also failed: $e');
            }
          }
        }
      }
    }

    final invoiceNo = DateTime.now().millisecondsSinceEpoch % 100000;

    pdf.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(32),
        build: (pw.Context context) {
          return pw.Column(
            crossAxisAlignment: pw.CrossAxisAlignment.start,
            children: [
              // ── HEADER: title left | stamp + meta right ──
              pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: [
                  pw.Column(
                    crossAxisAlignment: pw.CrossAxisAlignment.start,
                    children: [
                      pw.Text('Online Proforma Invoice',
                          style: pw.TextStyle(
                              fontSize: 18, fontWeight: pw.FontWeight.bold)),
                      pw.Text('File #${widget.fileNumber}',
                          style: const pw.TextStyle(
                              fontSize: 11, color: PdfColors.grey600)),
                    ],
                  ),
                  pw.Column(
                    crossAxisAlignment: pw.CrossAxisAlignment.end,
                    children: [
                      // ── Stamp badge (full opacity, always visible) ──
                      if (stampSvg != null)
                        _stampCircleFromSvg(stampSvg, 80)
                      else if (stampImg != null)
                        _stampCircle(stampImg, 80),
                      pw.SizedBox(height: 4),
                      pw.Text('Invoice No: $invoiceNo',
                          style: const pw.TextStyle(fontSize: 10)),
                      pw.Text(
                          'Date: ${DateTime.now().day}/${DateTime.now().month}/${DateTime.now().year}',
                          style: const pw.TextStyle(fontSize: 10)),
                    ],
                  ),
                ],
              ),
              pw.Divider(height: 20),

              // ── Shop / Garage info row ──
              pw.Row(
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: [
                  pw.Expanded(
                    child: pw.Column(
                      crossAxisAlignment: pw.CrossAxisAlignment.start,
                      children: [
                        pw.Text(typeLabel,
                            style: pw.TextStyle(
                                fontWeight: pw.FontWeight.bold,
                                fontSize: 11,
                                color: PdfColors.grey600)),
                        pw.SizedBox(height: 4),
                        pw.Text(applicant.name,
                            style: pw.TextStyle(
                                fontSize: 13,
                                fontWeight: pw.FontWeight.bold)),
                        if (applicant.storeId != null)
                          pw.Text('Store ID: ${applicant.storeId}',
                              style: const pw.TextStyle(fontSize: 10)),
                        if (applicant.tinNumber != null)
                          pw.Text('TIN: ${applicant.tinNumber}',
                              style: const pw.TextStyle(fontSize: 10)),
                        if (applicant.phone != null)
                          pw.Text('Phone: ${applicant.phone}',
                              style: const pw.TextStyle(fontSize: 10)),
                        if (applicant.location != null)
                          pw.Text('Location: ${applicant.location}',
                              style: const pw.TextStyle(fontSize: 10)),
                      ],
                    ),
                  ),
                  pw.Expanded(
                    child: pw.Column(
                      crossAxisAlignment: pw.CrossAxisAlignment.end,
                      children: [
                        pw.Text('Author:',
                            style: pw.TextStyle(
                                fontWeight: pw.FontWeight.bold,
                                fontSize: 11)),
                        pw.Text('etera',
                            style: const pw.TextStyle(fontSize: 10)),
                        pw.Text('portal.eteraet.com',
                            style: const pw.TextStyle(fontSize: 10)),
                        pw.Text('Addis Ababa, Ethiopia',
                            style: const pw.TextStyle(fontSize: 10)),
                        pw.SizedBox(height: 4),
                        pw.Text('Vehicle: ${widget.vehicleInfo}',
                            style: pw.TextStyle(
                                fontSize: 10,
                                fontWeight: pw.FontWeight.bold)),
                      ],
                    ),
                  ),
                ],
              ),

              pw.SizedBox(height: 20),

              // ── Parts table ──
              _buildPartsTable(ap),

              pw.SizedBox(height: 16),
              pw.Text(
                '* All prices do NOT include VAT (15%)',
                style: const pw.TextStyle(fontSize: 9, color: PdfColors.red700),
              ),

              pw.Spacer(),

              // ── Company stamp row above footer (mirrors web .company-stamp) ──
              if (stampSvg != null || stampImg != null) ...[
                pw.Row(
                  mainAxisAlignment: pw.MainAxisAlignment.start,
                  children: [
                    if (stampSvg != null)
                      _stampCircleFromSvg(stampSvg, 180, opacity: 0.75, rotation: 0.17)
                    else if (stampImg != null)
                      _stampCircle(stampImg, 180, opacity: 0.75, rotation: 0.17),
                  ],
                ),
                pw.SizedBox(height: 8),
              ],

              // ── Footer ──
              pw.Divider(),
              pw.Center(
                child: pw.Text(
                  'NOTE: Price is NOT including 15% VAT  |  etera  |  Tel: 011-470-7566  |  TIN: 0094205503',
                  style: const pw.TextStyle(
                      fontSize: 8, color: PdfColors.grey600),
                  textAlign: pw.TextAlign.center,
                ),
              ),
            ],
          );
        },
      ),
    );

    return pdf.save();
  }

  pw.Widget _buildPartsTable(ProformaApplication ap) {
    final headers = ['#', 'Part / No.', 'Component', 'Condition', 'Grade', 'Country', 'Qty', 'Unit Price', 'Total'];

    final rows = widget.parts.asMap().entries.map((e) {
      final part = e.value;
      final pricing = ap.partsPricing.cast<PartPricing?>().firstWhere(
        (p) => p?.carPartId == part.id,
        orElse: () => ap.partsPricing.length > e.key ? ap.partsPricing[e.key] : null,
      );
      return [
        '${e.key + 1}',
        part.number.isNotEmpty ? part.number : part.grade,
        part.component,
        part.condition,
        part.grade,
        part.country,
        '${part.quantity}',
        pricing != null ? '${pricing.unitPrice.toStringAsFixed(2)} ETB' : '—',
        pricing != null ? '${pricing.partTotal.toStringAsFixed(2)} ETB' : '—',
      ];
    }).toList();

    return pw.TableHelper.fromTextArray(
      headers: headers,
      data: rows,
      headerStyle: pw.TextStyle(
          fontWeight: pw.FontWeight.bold, fontSize: 9, color: PdfColors.white),
      headerDecoration: const pw.BoxDecoration(color: PdfColors.teal700),
      cellStyle: const pw.TextStyle(fontSize: 9),
      cellAlignment: pw.Alignment.centerLeft,
      columnWidths: {
        0: const pw.FixedColumnWidth(20),
        1: const pw.FlexColumnWidth(2),
        2: const pw.FlexColumnWidth(1.5),
        3: const pw.FlexColumnWidth(1.2),
        4: const pw.FlexColumnWidth(1),
        5: const pw.FlexColumnWidth(1),
        6: const pw.FixedColumnWidth(28),
        7: const pw.FlexColumnWidth(1.5),
        8: const pw.FlexColumnWidth(1.5),
      },
      // Totals footer rows
      tableWidth: pw.TableWidth.max,
    );
  }

  Future<void> _printOrDownload() async {
    setState(() => _generating = true);
    try {
      final bytes = await _buildPdf();
      final ap = widget.application;
      final name = '${ap.applicant.name}_invoice_${widget.fileNumber}'.replaceAll(' ', '_');
      await Printing.sharePdf(bytes: bytes, filename: '$name.pdf');
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Error: $e'), backgroundColor: EteraTheme.error),
        );
      }
    } finally {
      if (mounted) setState(() => _generating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final ap = widget.application;
    final isShop = ap.from == 'shop';
    final typeLabel = isShop ? 'Spare Part Shop' : 'Garage';
    final typeColor = isShop ? EteraTheme.green : EteraTheme.teal;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Invoice Preview'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          TextButton.icon(
            icon: _generating
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                        strokeWidth: 2, color: Colors.white))
                : const Icon(Icons.download, color: Colors.white, size: 20),
            label: Text(
              _generating ? 'Generating...' : 'Download PDF',
              style: const TextStyle(color: Colors.white, fontSize: 13),
            ),
            onPressed: _generating ? null : _printOrDownload,
          ),
        ],
      ),
      body: PdfPreview(
        build: (_) => _buildPdf(),
        allowPrinting: true,
        allowSharing: true,
        canChangeOrientation: false,
        canChangePageFormat: false,
        canDebug: false,
        pdfFileName:
            '${ap.applicant.name}_invoice_${widget.fileNumber}.pdf'.replaceAll(' ', '_'),
        actions: const [],
        initialPageFormat: PdfPageFormat.a4,
      ),
    );
  }
}
