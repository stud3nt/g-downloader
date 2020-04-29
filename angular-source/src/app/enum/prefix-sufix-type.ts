export class PrefixSufixType {

    public static CustomText: string = 'custom-text';

    public static FileName: string = 'file-name';

    public static CategoryName: string = 'category-name';

    public static NodeName: string = 'node-name';

    public static NodeSymbol: string = 'node-symbol';

	public static getData(): {} {
	    return {
	        'Specified name': this.CustomText,
            'File name': this.FileName,
            'Category name': this.CategoryName,
            'Node title': this.NodeName,
            'Node symbol': this.NodeSymbol
        };
    }

    public static getIterableData(): {name: string, type: string}[] {
        return [
            {
                name: 'Specific name',
                type: this.CustomText
            },
            {
                name: 'File name',
                type: this.FileName
            },
            {
                name: 'Category name',
                type: this.CategoryName
            },
            {
                name: 'File node title',
                type: this.NodeName
            },
            {
                name: 'File node symbol',
                type: this.NodeSymbol
            },
        ]
    }

}
