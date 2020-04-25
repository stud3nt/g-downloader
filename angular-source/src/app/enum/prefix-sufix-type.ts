export class PrefixSufixType {

    public static CustomText: string = '{custom-text}';

    public static FileName: string = '{file-name}';

    public static NodeName: string = '{node-name}';

    public static NodeSymbol: string = '{node-symbol}';

	public static getData(): {} {
	    return {
	        'Specified name': this.CustomText,
            'File name': this.FileName,
            'Node name': this.NodeName,
            'Node symbol': this.NodeSymbol
        };
    }

    public static getIterableData(): {name: string, symbol: string}[] {
        return [
            {
                name: 'Specific name',
                symbol: this.CustomText
            },
            {
                name: 'File name',
                symbol: this.FileName
            },
            {
                name: 'Node name',
                symbol: this.NodeName
            },
            {
                name: 'Node symbol',
                symbol: this.NodeSymbol
            },
        ]
    }

}
