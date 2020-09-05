export class TableSettings {

    private _page: number = 1;

    private _perPage: number = 500;

    private _firstPage: number = 1;

    private _lastPage: number = 1;

    private _offset: number = 0;

    private _pagesNumbers: object = [];

    private _sortBy: string = 'id';

    private _sortDesc: boolean = true;

    private _searchPhrase: string = null;

    private _totalResults: number = 1;

    private _result: object = {
        from: 0,
        to: 0,
        count: 0
    }

    private _data = null;

    get page(): number {
        return this._page;
    }

    set page(value: number) {
        this._page = value;
    }

    get perPage(): number {
        return this._perPage;
    }

    set perPage(value: number) {
        this._perPage = value;
    }

    get firstPage(): number {
        return this._firstPage;
    }

    set firstPage(value: number) {
        this._firstPage = value;
    }

    get lastPage(): number {
        return this._lastPage;
    }

    set lastPage(value: number) {
        this._lastPage = value;
    }

    get pagesNumbers(): object {
        return this._pagesNumbers;
    }

    set pagesNumbers(value: object) {
        this._pagesNumbers = value;
    }

    get sortBy(): string {
        return this._sortBy;
    }

    set sortBy(value: string) {
        this._sortBy = value;
    }

    get sortDesc(): boolean {
        return this._sortDesc;
    }

    set sortDesc(value: boolean) {
        this._sortDesc = value;
    }

    get searchPhrase(): string {
        return this._searchPhrase;
    }

    set searchPhrase(value: string) {
        this._searchPhrase = value;
    }

    get totalResults(): number {
        return this._totalResults;
    }

    set totalResults(value: number) {
        this._totalResults = value;
    }

    get offset(): number {
        return this._offset;
    }

    set offset(value: number) {
        this._offset = value;
    }

    get data(): any {
        return this._data;
    }

    set data(value: any) {
        this._data = value;
    }

    get result(): object {
        return this._result;
    }

    set result(value: object) {
        this._result = value;
    }
}
