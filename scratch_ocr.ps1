Add-Type -AssemblyName System.Runtime.WindowsRuntime

# Load WinRT classes
[Windows.Media.Ocr.OcrEngine, Windows.Media, ContentType=WindowsRuntime] | Out-Null
[Windows.Graphics.Imaging.BitmapDecoder, Windows.Graphics, ContentType=WindowsRuntime] | Out-Null
[Windows.Storage.StorageFile, Windows.Storage, ContentType=WindowsRuntime] | Out-Null

$filePath = "D:\dev\admision\api-admision\public\img\matricula.jpg"

# Helper function to await WinRT async operations
function Await-WinRT($asyncOp) {
    $awaiter = [System.Runtime.InteropServices.WindowsRuntime.WindowsRuntimeSystemExtensions]::GetAwaiter($asyncOp)
    return $awaiter.GetResult()
}

try {
    # Get StorageFile
    $asyncOp1 = [Windows.Storage.StorageFile]::GetFileFromPathAsync($filePath)
    $file = Await-WinRT $asyncOp1

    # Open file stream
    $asyncOp2 = $file.OpenAsync([Windows.Storage.FileAccessMode]::Read)
    $stream = Await-WinRT $asyncOp2

    # Decode image
    $asyncOp3 = [Windows.Graphics.Imaging.BitmapDecoder]::CreateAsync($stream)
    $decoder = Await-WinRT $asyncOp3

    # Get SoftwareBitmap
    $asyncOp4 = $decoder.GetSoftwareBitmapAsync()
    $bitmap = Await-WinRT $asyncOp4

    # Run OCR
    $engine = [Windows.Media.Ocr.OcrEngine]::TryCreateFromUserProfileLanguages()
    $asyncOp5 = $engine.RecognizeAsync($bitmap)
    $ocrResult = Await-WinRT $asyncOp5

    Write-Output "OCR_START"
    Write-Output $ocrResult.Text
    Write-Output "OCR_END"
} catch {
    Write-Error $_
}
