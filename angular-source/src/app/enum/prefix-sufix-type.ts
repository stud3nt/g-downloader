export class PrefixSufixType {

    public static CustomText: string = 'custom-text';

    public static FileDescription: string = 'file-description';

    public static CategoryName: string = 'category-name';

    public static NodeName: string = 'node-name';

    public static NodeSymbol: string = 'node-symbol';

	public static getData(): {} {
	    return {
	        'Specified name': this.CustomText,
            'File title/description': this.FileDescription,
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
                name: 'File title/description',
                type: this.FileDescription
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
