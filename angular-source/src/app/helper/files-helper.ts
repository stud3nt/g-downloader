export class FilesHelper {

    public filesSizesUnit: { name: string, symbol: string, multiplier: number }[] = [
        {
            name: 'Bytes',
            symbol: 'B',
            multiplier: 1
        },
        {
            name: 'KiloBytes',
            symbol: 'KB',
            multiplier: 1024
        },
        {
            name: 'MegaBytes',
            symbol: 'MB',
            multiplier: (1024 * 1024)
        }
    ];

}
