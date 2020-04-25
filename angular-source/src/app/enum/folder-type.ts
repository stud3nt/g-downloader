export class FolderType {

    public static CustomName: string = '{custom-name}';

    public static NodeName: string = '{node-name}';

    public static NodeSymbol: string = '{node-symbol}';

	public static getData(): {} {
	    return {
	        'Specific name': this.CustomName,
            'Node name': this.NodeName,
            'Node symbol': this.NodeSymbol
        };
    }

    public static getIterableData(): {name: string, symbol: string}[] {
	    return [
            {
                name: 'Specific name',
                symbol: this.CustomName
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
