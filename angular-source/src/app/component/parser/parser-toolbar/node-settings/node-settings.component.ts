import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {ParserNodeSettings} from "../../../../model/parser-node-settings";
import {FilesHelper} from "../../../../helper/files-helper";
import {PrefixSufixType} from "../../../../enum/prefix-sufix-type";
import {FolderType} from "../../../../enum/folder-type";

@Component({
    selector: 'node-settings',
    templateUrl: './node-settings.component.html',
    styleUrls: ['./node-settings.component.scss']
})
export class NodeSettingsComponent implements OnInit {

    public filesHelper = new FilesHelper();
    public PrefixSufixType = PrefixSufixType;
    public FolderType = FolderType;

    public prefixes: {name: string, symbol: string}[] = [];
    public folders: {name: string, symbol: string}[] = [];

    public sizeUnit: string = '';
    public prefixType: string = '';
    public sufixType: string = '';
    public folderType: string = '';

    public sizeInputValue: number = 0;
    public prefixInputValue: string = '';
    public sufixInputValue: string = '';
    public folderInputValue: string = '';

    public prefixLabel: string = '';
    public sufixLabel: string = '';
    public folderLabel: string = '';

    public _nodeSettings: ParserNodeSettings = null;
    private _tmpNodeSettings: ParserNodeSettings = null;

    @Input() set nodeSettings(settings: ParserNodeSettings) {
        this._nodeSettings = settings;
        this._tmpNodeSettings = settings;

        if (settings.maxSize > 0)
            for (let unit of this.filesHelper.filesSizesUnit) {
                let modulo = settings.maxSize % unit.multiplier;

                if (settings.maxSize > unit.multiplier && modulo === 0) {
                    this.sizeUnit = unit.name;
                    this.sizeInputValue = (settings.maxSize / unit.multiplier);
                }

            }
        else
            this.sizeUnit = this.filesHelper.filesSizesUnit[0].name;

        for (let prefix of this.prefixes)
            if (settings.prefix === prefix.symbol) {
                this.prefixLabel = prefix.name;
                this.prefixType = prefix.symbol;
            }

        for (let sufix of this.prefixes)
            if (settings.sufix === sufix.symbol) {
                this.sufixLabel = sufix.name;
                this.sufixType = sufix.symbol;
            }

        for (let folder of this.folders)
            if (settings.folder === folder.symbol) {
                this.folderLabel = folder.name;
                this.folderType = folder.symbol;
            }

        this.prefixLabel = this.prefixLabel || this.prefixes[0].name;
        this.prefixType = this.folderType || this.prefixes[0].symbol;
        this.sufixLabel = this.sufixLabel || this.prefixes[0].name;
        this.sufixType = this.sufixType || this.prefixes[0].symbol;
        this.folderLabel = this.folderLabel || this.folders[0].name;
        this.folderType = this.folderType || this.folders[0].symbol;

        this.recalculateMaxSize();

        this.prefixInputValue = (this.prefixType === PrefixSufixType.CustomText) ? settings.prefix : '';
        this.sufixInputValue = (this.sufixType === PrefixSufixType.CustomText) ? settings.sufix : '';
        this.folderInputValue = (this.folderType === FolderType.CustomName) ? settings.folder : '';
    }

    @Output() onSettingsChange = new EventEmitter<ParserNodeSettings>()

    constructor() {
        this.prefixes = PrefixSufixType.getIterableData();
        this.folders = FolderType.getIterableData();
    }

    ngOnInit() {}

    /**
     * Calculates real max size (in bytes) based on input value and selected unit;
     */
    public recalculateMaxSize() {
        let value = this.sizeInputValue;

        for (let unit of this.filesHelper.filesSizesUnit) {
            if (this.sizeUnit === unit.name) {
                this._nodeSettings.maxSize = (value * unit.multiplier);
                return;
            }
        }

        this._nodeSettings.maxSize = value;
    }

    public resetSettings(): void {
        this._nodeSettings = this._tmpNodeSettings;
    }

    public saveSettings(): void {
        this._nodeSettings.prefix = (this.prefixType === PrefixSufixType.CustomText) ? this.prefixInputValue : this.prefixType;
        this._nodeSettings.sufix = (this.sufixType === PrefixSufixType.CustomText) ? this.sufixInputValue : this.sufixType;
        this._nodeSettings.folder = (this.folderType === FolderType.CustomName) ? this.folderInputValue : this.folderType;

        this.onSettingsChange.next(this._nodeSettings);
    }

}
