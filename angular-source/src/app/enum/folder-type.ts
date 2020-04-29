export class FolderType {

    public static CustomName: string = 'custom-name';

    public static NodeName: string = 'node-name';

    public static CategoryName: string = 'category-name';

    public static NodeSymbol: string = 'node-symbol';

	public static getData(): {} {
	    return {
	        'Specific name': this.CustomName,
            'Node name': this.NodeName,
            'Category name': this.CategoryName,
            'Node symbol': this.NodeSymbol
        };
    }

    public static getIterableData(): {name: string, type: string}[] {
	    return [
            {
                name: 'Specific name',
                type: this.CustomName
            },
            {
                name: 'Category name',
                type: this.CategoryName
            },
            {
                name: 'Node name',
                type: this.NodeName
            },
            {
                name: 'Node symbol',
                type: this.NodeSymbol
            },
        ]
    }

}
